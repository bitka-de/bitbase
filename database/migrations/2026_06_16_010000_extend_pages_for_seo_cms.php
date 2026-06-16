<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique('pages_slug_unique');

            $table->string('h1')->nullable()->after('title');
            $table->string('status', 20)->default('draft')->after('content');
            $table->timestamp('updated_content_at')->nullable()->after('published_at');

            $table->string('seo_title')->nullable()->after('updated_content_at');
            $table->text('meta_description')->nullable()->after('seo_title');
            $table->string('canonical_url')->nullable()->after('meta_description');
            $table->string('robots_index', 20)->default('index')->after('canonical_url');
            $table->string('robots_follow', 20)->default('follow')->after('robots_index');

            $table->string('og_title')->nullable()->after('robots_follow');
            $table->text('og_description')->nullable()->after('og_title');
            $table->unsignedBigInteger('og_image_id')->nullable()->after('og_description');
            $table->string('twitter_title')->nullable()->after('og_image_id');
            $table->text('twitter_description')->nullable()->after('twitter_title');
            $table->unsignedBigInteger('twitter_image_id')->nullable()->after('twitter_description');

            $table->string('schema_type', 50)->nullable()->after('twitter_image_id');
            $table->json('schema_data')->nullable()->after('schema_type');

            $table->string('locale', 10)->default('de')->after('schema_data');
            $table->foreignId('translation_group_id')->nullable()->constrained('translation_groups')->nullOnDelete()->after('locale');
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete()->after('translation_group_id');
            $table->string('template')->nullable()->after('parent_id');
            $table->integer('sort_order')->default(0)->after('template');

            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete()->after('sort_order');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete()->after('author_id');

            $table->json('redirect_old_urls')->nullable()->after('reviewer_id');
            $table->boolean('sitemap_include')->default(true)->after('redirect_old_urls');
            $table->decimal('sitemap_priority', 3, 1)->default(0.5)->after('sitemap_include');
            $table->string('sitemap_changefreq', 20)->default('weekly')->after('sitemap_priority');

            $table->index(['status', 'published_at']);
            $table->index(['locale', 'status']);
            $table->unique(['locale', 'parent_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['locale', 'parent_id', 'slug']);
            $table->dropIndex(['status', 'published_at']);
            $table->dropIndex(['locale', 'status']);

            $table->unique('slug');

            $table->dropConstrainedForeignId('reviewer_id');
            $table->dropConstrainedForeignId('author_id');
            $table->dropConstrainedForeignId('parent_id');
            $table->dropConstrainedForeignId('translation_group_id');

            $table->dropColumn([
                'h1',
                'status',
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
                'template',
                'sort_order',
                'redirect_old_urls',
                'sitemap_include',
                'sitemap_priority',
                'sitemap_changefreq',
            ]);
        });
    }
};