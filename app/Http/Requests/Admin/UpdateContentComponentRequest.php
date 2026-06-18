<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentComponentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $incomingTags = $this->input('tags');

        if (is_string($incomingTags)) {
            $normalizedTags = collect(explode(',', $incomingTags))
                ->map(fn ($tag) => trim((string) $tag))
                ->filter(fn ($tag) => $tag !== '')
                ->map(fn ($tag) => mb_strtolower($tag))
                ->unique()
                ->values()
                ->all();

            $this->merge([
                'tags' => $normalizedTags,
            ]);
        }
    }

    public function authorize(): bool
    {
        $component = $this->route('component');

        return $this->user()?->can('update', $component) ?? false;
    }

    public function rules(): array
    {
        $component = $this->route('component');

        return [
            'title' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('content_components', 'name')->ignore($component)],
            'description' => ['nullable', 'string', 'max:320'],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:30'],
            'content' => ['required', 'string'],
            'css' => ['nullable', 'string'],
            'js' => ['nullable', 'string'],
        ];
    }
}