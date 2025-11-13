<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard');
    }

    /**
     * Get dashboard statistics for the main dashboard.
     */
    public function getDashboardStats()
    {
        // Get total equipment count
        $totalEquipment = Equipment::count();

        // Get requested equipment count (pending reservations)
        $requestedEquipment = Reservation::where('status', 'pending')->count();

        // Get issued equipment count (currently issued equipment)
        $issuedEquipment = Reservation::where('status', 'issued')->count();

        // Get return requests count (equipment requested for return)
        $returnRequests = Reservation::where('status', 'return_requested')->count();

        // Get total students only
        $totalStudents = User::where('role', 'student')->count();

        // Get recent reservations for the dashboard
        $recentReservations = Reservation::with(['user', 'items.equipment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($reservation) {
                // Safe null checks to prevent "Attempt to read property on null" errors
                $firstItem = $reservation->items->first();
                $equipmentName = 'Multiple items';

                if ($firstItem && $firstItem->equipment) {
                    $equipmentName = $firstItem->equipment->name ?? 'Unknown Equipment';
                }

                return [
                    'id' => $reservation->id,
                    'name' => $equipmentName,
                    'user' => $reservation->user ? ($reservation->user->name ?? 'Unknown User') : 'Unknown User',
                    'date' => \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y'),
                    'time' => \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($reservation->end_time)->format('g:i A'),
                    'status' => $reservation->status,
                ];
            });

        return response()->json([
            'stats' => [
                'equipments' => $totalEquipment,
                'requested_equipments' => $requestedEquipment,
                'issued_equipments' => $issuedEquipment,
                'return_requests' => $returnRequests,
                'students' => $totalStudents,
            ],
            'recent_reservations' => $recentReservations
        ]);
    }

    /**
     * Get recent reservations for real-time updates.
     */
    public function getRecentReservations()
    {
        $recentReservations = Reservation::with(['user', 'items.equipment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($reservation) {
                // Safe null checks to prevent "Attempt to read property on null" errors
                $firstItem = $reservation->items->first();
                $equipmentName = 'Multiple items';

                if ($firstItem && $firstItem->equipment) {
                    $equipmentName = $firstItem->equipment->name ?? 'Unknown Equipment';
                }

                return [
                    'id' => $reservation->id,
                    'name' => $equipmentName,
                    'user' => $reservation->user ? ($reservation->user->name ?? 'Unknown User') : 'Unknown User',
                    'date' => \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y'),
                    'time' => \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($reservation->end_time)->format('g:i A'),
                    'status' => $reservation->status,
                ];
            });

        return response()->json([
            'recent_reservations' => $recentReservations
        ]);
    }

    /**
     * Get all dashboard data in one request for better performance.
     */
    public function getAllData()
    {
        $statsData = $this->getDashboardStats();
        return $statsData;
    }
}
