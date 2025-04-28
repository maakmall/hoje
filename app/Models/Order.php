<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
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
        'user_id',
        'reservation_id',
        'table_id',
        'datetime',
    ];

    /**
     * The user that made the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The reservation that the order is for.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * The table that the order is for.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * The menus that are part of the order.
     */
    public function orderMenus(): HasMany
    {
        return $this->hasMany(OrderMenu::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
