<?php

namespace App\Models;

use App\Enums\VariantBeverage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMenu extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'pemesanan_menu';

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
        'id_pemesanan',
        'id_menu',
        'variasi_minuman',
        'jumlah',
        'subtotal_harga',
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

    /**
     * The order that belongs to the order menu.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_pemesanan');
    }

    /**
     * The menu that belongs to the order menu.
     *
     * @return BelongsTo
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }
}
