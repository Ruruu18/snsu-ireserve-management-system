<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'equipment_id',
        'quantity',
        'status',
        'notes',
        'issued_at',
        'returned_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
        'quantity' => 'integer',
    ];

    /**
     * Get the reservation this item belongs to.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the equipment for this reservation item.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Check if this item has been issued.
     */
    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    /**
     * Check if this item has been returned.
     */
    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    /**
     * Check if this item is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
