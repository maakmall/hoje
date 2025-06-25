<?php

namespace App\Models;

use App\Enums\MenuCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'menu';

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
        'nama',
        'deskripsi',
        'gambar',
        'kategori',
        'tersedia'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kategori' => MenuCategory::class,
            'tersedia' => 'bool',
        ];
    }

    /**
     * Get the menu prices for the menu.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(MenuPrice::class, 'id_menu');
    }

    /**
     * Get the food menu items.
     */
    public function scopeFood(Builder $query): Builder
    {
        return $query->where('kategori', MenuCategory::Food);
    }

    /**
     * Get the beverage menu items.
     */
    public function scopeBeverage(Builder $query): Builder
    {
        return $query->where('kategori', MenuCategory::Beverage);
    }

    /**
     * Get the available menu items.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('tersedia', true);
    }
}
