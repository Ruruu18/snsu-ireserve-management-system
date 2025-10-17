<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reservation_code',
        'status',
        'purpose',
        'reservation_date',
        'start_time',
        'end_time',
        'notes',
        'admin_notes',
        'qr_code_data',
        'qr_code_path',
        'approved_at',
        'approved_by',
        'issued_at',
        'returned_at',
        'return_requested_at',
        'issued_by',
        'returned_by',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
        'return_requested_at' => 'datetime',
        'qr_code_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (!$reservation->reservation_code) {
                $reservation->reservation_code = 'RES-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Get the user that owns the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reservation items (equipment) for this reservation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    /**
     * Get the admin who issued the equipment.
     */
    public function issuedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the admin who processed the return.
     */
    public function returnedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /**
     * Scope a query to only include pending reservations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved reservations.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include completed reservations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include cancelled reservations.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get the duration of the reservation in hours.
     */
    public function getDurationAttribute()
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        return $start->diffInHours($end);
    }

    /**
     * Check if the reservation is active (issued and not returned).
     */
    public function isActive()
    {
        return $this->status === 'issued' && !$this->returned_at;
    }

    /**
     * Check if the reservation can be cancelled.
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'approved']) &&
               $this->reservation_date >= now()->toDateString();
    }

    /**
     * Get the total quantity of items in this reservation.
     */
    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Generate QR code data for this reservation.
     */
    public function generateQRData()
    {
        // Minimal data to fit in QR code - just reservation code and signature
        return [
            'code' => $this->reservation_code,
            'sig' => substr(hash('sha256', $this->reservation_code . config('app.key')), 0, 16),
        ];
    }
}
