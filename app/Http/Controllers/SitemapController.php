<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(SitemapService $sitemapService): Response
    {
        $xml = Cache::remember('seo:sitemap:pages', now()->addHours(6), fn (): string => $sitemapService->pagesXml());

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
