<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StudentDashboardController extends Controller
{
    /**
     * Display the student dashboard.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // Get user's reservations with equipment details
        $recentReservations = $user->reservations()
            ->with('equipment')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'equipment_name' => $reservation->equipment->name,
                    'date' => $reservation->reservation_date->format('M d, Y'),
                    'start_time' => \Carbon\Carbon::parse($reservation->start_time)->format('g:i A'),
                    'end_time' => \Carbon\Carbon::parse($reservation->end_time)->format('g:i A'),
                    'status' => $reservation->status,
                    'purpose' => $reservation->purpose,
                    'can_cancel' => $reservation->canBeCancelled(),
                ];
            });

        // Get statistics
        $stats = [
            'total_reservations' => $user->reservations()->count(),
            'pending_reservations' => $user->pendingReservations()->count(),
            'active_reservations' => $user->activeReservations()->count(),
            'completed_reservations' => $user->reservations()->where('status', 'completed')->count(),
        ];

        // Get upcoming reservations
        $upcomingReservations = $user->reservations()
            ->with('equipment')
            ->where('status', 'approved')
            ->where('reservation_date', '>=', now()->toDateString())
            ->orderBy('reservation_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(3)
            ->get();

        // Get available equipment grouped by category
        $availableEquipment = Equipment::where('status', 'available')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category')
            ->map(function ($equipment, $category) {
                return [
                    'category' => $category,
                    'equipment' => $equipment->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'image' => $item->image,
                            'location' => $item->location,
                        ];
                    })
                ];
            })
            ->values();

        return Inertia::render('StudentDashboard', [
            'stats' => $stats,
            'recentReservations' => $recentReservations,
            'upcomingReservations' => $upcomingReservations,
            'availableEquipment' => $availableEquipment,
        ]);
    }

    /**
     * Display the equipment catalog for students.
     */
    public function equipmentCatalog(Request $request): Response
    {
        $user = Auth::user();

        // Get available equipment grouped by category
        $availableEquipment = Equipment::where('status', 'available')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category')
            ->map(function ($equipment, $category) {
                return [
                    'category' => $category,
                    'equipment' => $equipment->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'image' => $item->image,
                            'location' => $item->location,
                            'category' => $item->category,
                            'serial_number' => $item->serial_number,
                        ];
                    })
                ];
            })
            ->values();

        // Get user stats for sidebar
        $stats = [
            'total_reservations' => $user->reservations()->count(),
            'pending_reservations' => $user->pendingReservations()->count(),
            'active_reservations' => $user->activeReservations()->count(),
            'completed_reservations' => $user->reservations()->where('status', 'completed')->count(),
        ];

        return Inertia::render('Student/EquipmentCatalog', [
            'availableEquipment' => $availableEquipment,
            'stats' => $stats,
        ]);
    }

    /**
     * Display equipment issued to the student.
     */
    public function issuedEquipment(Request $request): Response
    {
        $user = Auth::user();

        // Get equipment currently issued to the student (approved reservations)
        $issuedEquipment = $user->reservations()
            ->with('equipment')
            ->where('status', 'approved')
            ->where('reservation_date', '<=', now()->toDateString())
            ->whereTime('start_time', '<=', now()->toTimeString())
            ->orderBy('reservation_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10)
            ->through(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'equipment_id' => $reservation->equipment->id,
                    'equipment_name' => $reservation->equipment->name,
                    'equipment_image' => $reservation->equipment->image,
                    'equipment_category' => $reservation->equipment->category,
                    'equipment_location' => $reservation->equipment->location,
                    'date' => $reservation->reservation_date->format('M d, Y'),
                    'start_time' => \Carbon\Carbon::parse($reservation->start_time)->format('g:i A'),
                    'end_time' => \Carbon\Carbon::parse($reservation->end_time)->format('g:i A'),
                    'purpose' => $reservation->purpose,
                    'issued_date' => $reservation->created_at->format('M d, Y g:i A'),
                    'can_return' => true, // Students can request return
                ];
            });

        // Get user stats for sidebar
        $stats = [
            'total_reservations' => $user->reservations()->count(),
            'pending_reservations' => $user->pendingReservations()->count(),
            'active_reservations' => $user->activeReservations()->count(),
            'completed_reservations' => $user->reservations()->where('status', 'completed')->count(),
        ];

        return Inertia::render('Student/IssuedEquipment', [
            'issuedEquipment' => $issuedEquipment,
            'stats' => $stats,
        ]);
    }

    /**
     * Display requested equipment with status tracking.
     */
    public function requestedEquipment(Request $request): Response
    {
        $user = Auth::user();

        // Get all reservations (requests) made by the student
        $requestedEquipment = $user->reservations()
            ->with('equipment')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'equipment_id' => $reservation->equipment->id,
                    'equipment_name' => $reservation->equipment->name,
                    'equipment_image' => $reservation->equipment->image,
                    'equipment_category' => $reservation->equipment->category,
                    'date' => $reservation->reservation_date->format('M d, Y'),
                    'start_time' => \Carbon\Carbon::parse($reservation->start_time)->format('g:i A'),
                    'end_time' => \Carbon\Carbon::parse($reservation->end_time)->format('g:i A'),
                    'status' => $reservation->status,
                    'purpose' => $reservation->purpose,
                    'admin_notes' => $reservation->admin_notes,
                    'created_at' => $reservation->created_at->format('M d, Y g:i A'),
                    'updated_at' => $reservation->updated_at->format('M d, Y g:i A'),
                    'can_cancel' => $reservation->canBeCancelled(),
                ];
            });

        // Get user stats for sidebar
        $stats = [
            'total_reservations' => $user->reservations()->count(),
            'pending_reservations' => $user->pendingReservations()->count(),
            'active_reservations' => $user->activeReservations()->count(),
            'completed_reservations' => $user->reservations()->where('status', 'completed')->count(),
        ];

        return Inertia::render('Student/RequestedEquipment', [
            'requestedEquipment' => $requestedEquipment,
            'stats' => $stats,
        ]);
    }
}
