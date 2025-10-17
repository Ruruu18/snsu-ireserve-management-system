<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'status',
        'serial_number',
        'image',
        'total_quantity',
    ];

    protected $casts = [
        'status' => 'string',
        'total_quantity' => 'integer',
    ];

    /**
     * Get the reservation items for this equipment.
     */
    public function reservationItems(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    /**
     * Get the reservations for this equipment through reservation items.
     */
    public function reservations(): HasManyThrough
    {
        return $this->hasManyThrough(
            Reservation::class,
            ReservationItem::class,
            'equipment_id',
            'id',
            'id',
            'reservation_id'
        );
    }

    /**
     * Check if equipment is available for a specific date and time.
     */
    public function isAvailableFor($date, $startTime, $endTime, $excludeReservationId = null)
    {
        // Check through reservation items to see if this equipment is reserved
        $query = ReservationItem::where('equipment_id', $this->id)
            ->whereHas('reservation', function ($q) use ($date, $startTime, $endTime, $excludeReservationId) {
                $q->where('reservation_date', $date)
                  ->where('status', '!=', 'cancelled')
                  ->where(function ($q2) use ($startTime, $endTime) {
                      $q2->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q3) use ($startTime, $endTime) {
                            $q3->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                  });

                if ($excludeReservationId) {
                    $q->where('id', '!=', $excludeReservationId);
                }
            });

        return $query->count() === 0 && $this->status === 'available';
    }

    /**
     * Get available equipment for a specific date and time.
     */
    public static function getAvailableFor($date, $startTime, $endTime, $category = null)
    {
        $query = self::where('status', 'available');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->get()->filter(function ($equipment) use ($date, $startTime, $endTime) {
            return $equipment->isAvailableFor($date, $startTime, $endTime);
        });
    }

    /**
     * Get currently issued quantity (equipment currently in use).
     */
    public function getCurrentlyIssuedQuantity()
    {
        return $this->reservationItems()
            ->where('status', 'issued')
            ->whereHas('reservation', function ($query) {
                $query->where('status', 'issued');
            })
            ->sum('quantity');
    }

    /**
     * Get available quantity (not currently issued).
     */
    public function getAvailableQuantity()
    {
        return $this->total_quantity - $this->getCurrentlyIssuedQuantity();
    }

    /**
     * Get pending quantity (approved but not yet issued).
     */
    public function getPendingQuantity()
    {
        return $this->reservationItems()
            ->where('status', 'pending')
            ->whereHas('reservation', function ($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
            ->sum('quantity');
    }

    /**
     * Get total reserved quantity (pending + issued).
     */
    public function getTotalReservedQuantity()
    {
        return $this->reservationItems()
            ->whereIn('status', ['pending', 'issued'])
            ->whereHas('reservation', function ($query) {
                $query->whereIn('status', ['pending', 'approved', 'issued']);
            })
            ->sum('quantity');
    }

    /**
     * Get usage statistics for this equipment.
     */
    public function getUsageStats()
    {
        $currentlyIssued = $this->getCurrentlyIssuedQuantity();
        $pending = $this->getPendingQuantity();
        $available = $this->getAvailableQuantity();

        return [
            'total_quantity' => $this->total_quantity,
            'currently_issued' => $currentlyIssued,
            'pending' => $pending,
            'available' => $available,
            'usage_percentage' => $this->total_quantity > 0 ? round(($currentlyIssued / $this->total_quantity) * 100, 1) : 0,
            'is_fully_utilized' => $available <= 0,
            'has_pending' => $pending > 0,
        ];
    }

    /**
     * Get current reservations with user details for this equipment.
     */
    public function getCurrentReservations()
    {
        return $this->reservationItems()
            ->whereHas('reservation', function ($query) {
                $query->whereIn('status', ['issued', 'return_requested']);
            })
            ->with(['reservation.user'])
            ->get()
            ->filter(function ($item) {
                return $item->reservation !== null;
            })
            ->map(function ($item) {
                return [
                    'id' => $item->reservation->id,
                    'reservation_code' => $item->reservation->reservation_code,
                    'student_name' => $item->reservation->user->name,
                    'student_email' => $item->reservation->user->email,
                    'quantity' => $item->quantity,
                    'issued_at' => $item->reservation->issued_at,
                    'reservation_date' => $item->reservation->reservation_date,
                    'start_time' => $item->reservation->start_time,
                    'end_time' => $item->reservation->end_time,
                    'purpose' => $item->reservation->purpose,
                    'status' => $item->reservation->status,
                ];
            });
    }
}
