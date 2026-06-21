<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'disk',
        'path',
        'name',
        'alt_text',
        'source',
        'tags',
        'title',
        'caption',
        'description',
        'copyright',
        'focal_point',
        'width',
        'height',
        'mime_type',
        'file_size',
        'variants',
    ];

    protected function casts(): array
    {
        return [
            'focal_point' => 'array',
            'tags' => 'array',
            'variants' => 'array',
        ];
    }

    public function getUrlAttribute(): string
    {
        return route('media.show', ['filename' => basename($this->path)]);
    }
}
