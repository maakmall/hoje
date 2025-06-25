<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'reservasi';

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
        'nama_pelanggan',
        'email_pelanggan',
        'telepon_pelanggan',
        'waktu',
        'id_lokasi',
        'jumlah_orang',
        'catatan',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
        ];
    }

    /**
     * The location that the reservation is for.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'id_lokasi');
    }

    /**
     * The order associated with the reservation.
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'id_reservasi');
    }

    public function orderMenus(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderMenu::class,
            Order::class,
            'id_reservasi',
            'id_pemesanan',
        );
    }
}
