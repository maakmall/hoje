<?php

namespace App\Models;

use App\Enums\VariantBeverage;
use Illuminate\Database\Eloquent\Model;

class MenuPrice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'harga_menu';

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
        'id_menu',
        'variasi_minuman',
        'harga'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variasi_minuman' => VariantBeverage::class,
        ];
    }
}
