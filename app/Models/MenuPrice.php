<?php

namespace App\Models;

use App\Enums\VariantBeverage;
use Illuminate\Database\Eloquent\Model;

class MenuPrice extends Model
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
        'menu_id',
        'variant_beverage',
        'price'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variant_beverage' => VariantBeverage::class,
        ];
    }
}
