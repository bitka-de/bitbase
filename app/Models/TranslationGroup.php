<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TranslationGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_key',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}
