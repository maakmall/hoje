<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'pemesanan';

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
        'id',
        'id_reservasi',
        'id_meja',
        'waktu',
    ];

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The reservation that the order is for.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'id_reservasi');
    }

    /**
     * The table that the order is for.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'id_meja');
    }

    /**
     * The menus that are part of the order.
     */
    public function orderMenus(): HasMany
    {
        return $this->hasMany(OrderMenu::class, 'id_pemesanan');
    }

    /**
     * The payment associated with the order
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'id_pemesanan');
    }
}
