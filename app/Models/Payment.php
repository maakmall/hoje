<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'pembayaran';

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
        'id_pemesanan',
        'jumlah',
        'metode',
        'status',
        'waktu',
        'id_transaksi',
        'akun_virtual',
        'tautan',
        'link',
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
            'metode' => PaymentMethod::class,
            'status' => PaymentStatus::class,
        ];
    }

    /**
     * Get the belongs to order relation
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_pemesanan');
    }
}
