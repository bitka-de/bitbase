<?php

namespace App\Jobs;

use App\Models\Page;
use App\Services\SeoAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunSeoAuditJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $pageId)
    {
    }

    public function handle(SeoAuditService $auditService): void
    {
        $page = Page::query()->find($this->pageId);

        if ($page !== null) {
            $auditService->run($page);
        }
    }
}
