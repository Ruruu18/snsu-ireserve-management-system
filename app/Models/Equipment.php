<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'status',
        'location',
        'serial_number',
        'image',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the reservations for this equipment.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Check if equipment is available for a specific date and time.
     */
    public function isAvailableFor($date, $startTime, $endTime, $excludeReservationId = null)
    {
        $query = $this->reservations()
            ->where('reservation_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

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
}
