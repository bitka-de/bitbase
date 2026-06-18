<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'name',
        'description',
        'tags',
        'content',
        'css',
        'js',
    ];

    protected $casts = [
        'tags' => 'array',
    ];
}