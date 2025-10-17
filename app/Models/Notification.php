<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Create a return request notification.
     */
    public static function createReturnRequest($reservation)
    {
        $itemCount = $reservation->items->count();
        $equipmentList = $reservation->items->take(3)->pluck('equipment.name')->join(', ');

        if ($itemCount > 3) {
            $equipmentList .= " and " . ($itemCount - 3) . " more items";
        }

        $message = $itemCount === 1
            ? "Student {$reservation->user->name} has requested to return {$equipmentList}"
            : "Student {$reservation->user->name} has requested to return {$itemCount} items: {$equipmentList}";

        return self::create([
            'type' => 'return_request',
            'title' => 'Equipment Return Requested',
            'message' => $message,
            'data' => [
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'equipment_name' => $equipmentList,
                'user_name' => $reservation->user->name,
                'item_count' => $itemCount,
                'total_quantity' => $reservation->items->sum('quantity'),
            ],
        ]);
    }

    /**
     * Create a new reservation notification.
     */
    public static function createNewReservation($reservation)
    {
        $itemCount = $reservation->items->count();
        $equipmentList = $reservation->items->take(3)->pluck('equipment.name')->join(', ');

        if ($itemCount > 3) {
            $equipmentList .= " and " . ($itemCount - 3) . " more items";
        }

        $message = "New reservation from {$reservation->user->name} for {$itemCount} item(s): {$equipmentList}";

        return self::create([
            'type' => 'new_reservation',
            'title' => 'New Equipment Reservation',
            'message' => $message,
            'data' => [
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'equipment_name' => $equipmentList,
                'user_name' => $reservation->user->name,
                'item_count' => $itemCount,
                'total_quantity' => $reservation->items->sum('quantity'),
            ],
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Create a cart addition notification.
     */
    public static function createCartAddition($user, $equipment, $quantity = 1)
    {
        return self::create([
            'type' => 'cart_addition',
            'title' => 'Equipment Added to Cart',
            'message' => "Student {$user->name} added {$equipment->name} to their cart",
            'data' => [
                'user_id' => $user->id,
                'equipment_id' => $equipment->id,
                'equipment_name' => $equipment->name,
                'user_name' => $user->name,
                'quantity' => $quantity,
            ],
        ]);
    }

    /**
     * Create a user registration notification.
     */
    public static function createUserRegistration($user)
    {
        return self::create([
            'type' => 'user_registration',
            'title' => 'New User Registration',
            'message' => "New user {$user->name} ({$user->email}) has registered",
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
            ],
        ]);
    }

    /**
     * Create a student creation notification (when admin creates student).
     */
    public static function createStudentCreation($student)
    {
        return self::create([
            'type' => 'student_creation',
            'title' => 'New Student Account Created',
            'message' => "New student account created for {$student->name} ({$student->email})",
            'data' => [
                'user_id' => $student->id,
                'user_name' => $student->name,
                'user_email' => $student->email,
                'department' => $student->department->name ?? 'No Department',
            ],
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public static function getUnreadCount()
    {
        return self::where('is_read', false)->count();
    }
}
