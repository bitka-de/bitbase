<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'score',
        'status',
        'issues',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'issues' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
