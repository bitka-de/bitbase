<?php

namespace App\Models;

use App\Enums\PageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'h1',
        'excerpt',
        'content',
        'status',
        'is_published',
        'published_at',
        'updated_content_at',
        'seo_title',
        'meta_description',
        'canonical_url',
        'robots_index',
        'robots_follow',
        'og_title',
        'og_description',
        'og_image_id',
        'twitter_title',
        'twitter_description',
        'twitter_image_id',
        'schema_type',
        'schema_data',
        'locale',
        'translation_group_id',
        'parent_id',
        'template',
        'sort_order',
        'author_id',
        'reviewer_id',
        'redirect_old_urls',
        'sitemap_include',
        'sitemap_priority',
        'sitemap_changefreq',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'updated_content_at' => 'datetime',
            'schema_data' => 'array',
            'redirect_old_urls' => 'array',
            'sitemap_include' => 'boolean',
            'sitemap_priority' => 'decimal:1',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function translationGroup(): BelongsTo
    {
        return $this->belongsTo(TranslationGroup::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(self::class, 'translation_group_id', 'translation_group_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest();
    }

    public function seoAudits(): HasMany
    {
        return $this->hasMany(SeoAudit::class)->latest();
    }

    public function latestSeoAudit(): HasOne
    {
        return $this->hasOne(SeoAudit::class)->latestOfMany();
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function twitterImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'twitter_image_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published->value)
            ->where(function (Builder $builder): void {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeIndexable(Builder $query): Builder
    {
        return $query->where('robots_index', 'index')
            ->where('sitemap_include', true);
    }

    public function getFullUrlAttribute(): string
    {
        $segments = [$this->slug];
        $cursor = $this->parent;

        while ($cursor !== null) {
            $segments[] = $cursor->slug;
            $cursor = $cursor->parent;
        }

        return url(implode('/', array_reverse($segments)));
    }

    public function getResolvedSeoTitleAttribute(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function getResolvedMetaDescriptionAttribute(): string
    {
        if (! empty($this->meta_description)) {
            return $this->meta_description;
        }

        if (! empty($this->excerpt)) {
            return Str::limit(trim(strip_tags($this->excerpt)), 155);
        }

        return Str::limit(trim(strip_tags((string) $this->content)), 155);
    }

    public function getPublicUrlAttribute(): string
    {
        return url($this->slug_path);
    }

    public function getSlugPathAttribute(): string
    {
        $segments = [$this->slug];
        $cursor = $this->parent;

        while ($cursor !== null) {
            $segments[] = $cursor->slug;
            $cursor = $cursor->parent;
        }

        return implode('/', array_reverse($segments));
    }
}