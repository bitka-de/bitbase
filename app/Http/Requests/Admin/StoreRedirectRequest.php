<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Redirect::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'old_url' => ['required', 'string', 'max:255', 'unique:redirects,old_url'],
            'new_url' => ['nullable', 'string', 'max:255'],
            'status_code' => ['required', 'integer', 'in:301,302,410'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
