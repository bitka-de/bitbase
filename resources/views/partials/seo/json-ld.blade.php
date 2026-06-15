@php
    $appName = config('app.name');
    $appUrl = rtrim(config('app.url', url('/')), '/');
    $currentUrl = url()->current();

    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $appName,
        'url' => $appUrl,
        'logo' => asset('favicon.ico'),
    ];

    $webSiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $appName,
        'url' => $appUrl,
        'inLanguage' => str_replace('_', '-', app()->getLocale()),
        'publisher' => [
            '@type' => 'Organization',
            'name' => $appName,
            'url' => $appUrl,
        ],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $appUrl . '/?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $webPageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $__env->yieldContent('meta_title', $appName),
        'url' => $__env->yieldContent('canonical_url', $currentUrl),
        'description' => $__env->yieldContent('meta_description', 'Willkommen auf unserer Website.'),
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => $appName,
            'url' => $appUrl,
        ],
    ];
@endphp

<script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($webSiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($webPageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>