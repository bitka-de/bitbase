<?php

namespace App\Services;

class RobotsService
{
    public function content(): string
    {
        if (! app()->environment('production')) {
            return implode("\n", [
                'User-agent: *',
                'Disallow: /',
            ]);
        }

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return implode("\n", $lines);
    }
}
