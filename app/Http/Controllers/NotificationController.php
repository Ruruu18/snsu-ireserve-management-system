<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications for admin.
     */
    public function index(Request $request)
    {
        $notifications = Notification::orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->format('M d, Y g:i A'),
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::getUnreadCount(),
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => Notification::getUnreadCount(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => Notification::getUnreadCount(),
        ]);
    }

    /**
     * Create cart addition notification.
     */
    public function createCartNotification(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'equipment_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();

        // Find the equipment
        $equipment = \App\Models\Equipment::find($request->equipment_id);

        if ($equipment) {
            Notification::createCartAddition($user, $equipment, $request->quantity);
        }

        return response()->json(['success' => true]);
    }
}
