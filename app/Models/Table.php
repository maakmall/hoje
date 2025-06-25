<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Table extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'meja';

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
        'nomor',
        'id_lokasi',
    ];

    /**
     * The location that the table belongs to.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'id_lokasi');
    }
}
