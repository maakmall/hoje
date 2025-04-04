<?php

namespace App\Models;

use App\Enums\MenuCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'image',
        'category',
        'availability'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => MenuCategory::class,
            'availability' => 'bool',
        ];
    }

    /**
     * Get the menu prices for the menu.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(MenuPrice::class);
    }
}
