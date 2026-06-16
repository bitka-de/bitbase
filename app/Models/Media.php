<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'disk',
        'path',
        'alt_text',
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
            'variants' => 'array',
        ];
    }

    public function getUrlAttribute(): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        return $disk->url($this->path);
    }
}
