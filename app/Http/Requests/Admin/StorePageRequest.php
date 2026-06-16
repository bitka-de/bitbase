<?php

namespace App\Http\Requests\Admin;

use App\Enums\PageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Page::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'h1' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(PageStatus::class)],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url'],
            'robots_index' => ['required', 'in:index,noindex'],
            'robots_follow' => ['required', 'in:follow,nofollow'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image_id' => ['nullable', 'integer', 'exists:media,id'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:320'],
            'twitter_image_id' => ['nullable', 'integer', 'exists:media,id'],
            'schema_type' => ['nullable', 'string', 'max:50'],
            'schema_data' => ['nullable', 'json'],
            'locale' => ['required', 'string', 'max:10'],
            'parent_id' => ['nullable', 'integer', 'exists:pages,id'],
            'template' => ['nullable', 'string', Rule::in(['default', 'focused', 'story'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'reviewer_id' => ['nullable', 'integer', 'exists:users,id'],
            'redirect_old_urls' => ['nullable', 'json'],
            'sitemap_include' => ['nullable', 'boolean'],
            'sitemap_priority' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'sitemap_changefreq' => ['nullable', 'in:always,hourly,daily,weekly,monthly,yearly,never'],
        ];
    }
}