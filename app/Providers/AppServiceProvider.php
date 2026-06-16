<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\Page;
use App\Models\Redirect;
use App\Observers\MediaObserver;
use App\Observers\PageObserver;
use App\Policies\MediaPolicy;
use App\Policies\PagePolicy;
use App\Policies\RedirectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(Redirect::class, RedirectPolicy::class);

        Page::observe(PageObserver::class);
        Media::observe(MediaObserver::class);
    }
}
