<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Notification;
use App\Models\Reservation;
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
            ->with(['items.equipment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($reservation) {
                $firstItem = $reservation->items->first();
                $totalItems = $reservation->items->sum('quantity');

                return [
                    'id' => $reservation->id,
                    'equipment_name' => $firstItem && $firstItem->equipment ? $firstItem->equipment->name : 'Multiple items',
                    'total_items' => $totalItems,
                    'items_count' => $reservation->items->count(),
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
            ->with('items.equipment')
            ->where('status', 'approved')
            ->where('reservation_date', '>=', now()->toDateString())
            ->orderBy('reservation_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(3)
            ->get()
            ->map(function ($reservation) {
                $firstItem = $reservation->items->first();
                $totalItems = $reservation->items->sum('quantity');

                return [
                    'id' => $reservation->id,
                    'equipment_name' => $firstItem && $firstItem->equipment ? $firstItem->equipment->name : 'Multiple items',
                    'total_items' => $totalItems,
                    'items_count' => $reservation->items->count(),
                    'date' => $reservation->reservation_date->format('M d, Y'),
                    'start_time' => \Carbon\Carbon::parse($reservation->start_time)->format('g:i A'),
                    'end_time' => \Carbon\Carbon::parse($reservation->end_time)->format('g:i A'),
                    'status' => $reservation->status,
                    'purpose' => $reservation->purpose,
                ];
            });

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

        // Get search parameters
        $search = $request->get('search');
        $category = $request->get('category');
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        // Start with available equipment query
        $query = Equipment::where('status', 'available');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%");
            });
        }

        // Apply category filter
        if ($category) {
            $query->where('category', $category);
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortDirection);

        // Get equipment with additional metadata
        $availableEquipment = $query->get()
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
                            'total_quantity' => $item->total_quantity ?? 1,
                            'available_quantity' => $item->getAvailableQuantity(),
                            'currently_issued' => $item->getCurrentlyIssuedQuantity(),
                            'created_at' => $item->created_at->format('M d, Y'),
                        ];
                    })
                ];
            })
            ->values();

        // Get all categories for filter dropdown
        $categories = Equipment::where('status', 'available')
            ->distinct()
            ->pluck('category')
            ->sort()
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
            'categories' => $categories,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Display equipment issued to the student.
     */
    public function issuedEquipment(Request $request): Response
    {
        $user = Auth::user();

        // Get equipment currently issued to the student (issued or return requested)
        $issuedEquipment = $user->reservations()
            ->with(['items.equipment'])
            ->whereIn('status', ['issued', 'return_requested'])
            ->orderBy('issued_at', 'desc')
            ->paginate(10)
            ->through(function ($reservation) {
                // For the issued equipment list, show reservation-level info with first item
                $firstItem = $reservation->items->first();
                $totalItems = $reservation->items->sum('quantity');

                return [
                    'id' => $reservation->id,
                    'equipment_id' => $firstItem && $firstItem->equipment ? $firstItem->equipment->id : null,
                    'equipment_name' => $firstItem && $firstItem->equipment ? $firstItem->equipment->name : 'Multiple items',
                    'equipment_image' => $firstItem && $firstItem->equipment ? $firstItem->equipment->image : null,
                    'equipment_category' => $firstItem && $firstItem->equipment ? $firstItem->equipment->category : null,
                    'equipment_location' => $firstItem && $firstItem->equipment ? $firstItem->equipment->location : null,
                    'total_items' => $totalItems,
                    'items_count' => $reservation->items->count(),
                    'items' => $reservation->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->equipment->name,
                            'image' => $item->equipment->image,
                            'category' => $item->equipment->category,
                            'quantity' => $item->quantity,
                            'status' => $item->status,
                        ];
                    }),
                    'date' => $reservation->reservation_date->format('M d, Y'),
                    'start_time' => \Carbon\Carbon::parse($reservation->start_time)->format('g:i A'),
                    'end_time' => \Carbon\Carbon::parse($reservation->end_time)->format('g:i A'),
                    'purpose' => $reservation->purpose,
                    'status' => $reservation->status,
                    'issued_at' => $reservation->issued_at ? $reservation->issued_at->format('M d, Y g:i A') : null,
                    'issued_date' => $reservation->issued_at ? $reservation->issued_at->format('M d, Y') : null,
                    'expected_return' => $reservation->reservation_date->format('M d, Y') . ' ' . \Carbon\Carbon::parse($reservation->end_time)->format('g:i A'),
                    'can_return' => $reservation->status === 'issued',
                    'return_requested' => $reservation->status === 'return_requested',
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

        // Get all reservations (requests) made by the student with their items
        $requestedEquipment = $user->reservations()
            ->with(['items.equipment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($reservation) {
                // For multiple items, we'll show the first item's details but include total count
                $firstItem = $reservation->items->first();
                $totalItems = $reservation->items->sum('quantity');

                return [
                    'id' => $reservation->id,
                    'equipment_id' => $firstItem && $firstItem->equipment ? $firstItem->equipment->id : null,
                    'equipment_name' => $firstItem && $firstItem->equipment ? $firstItem->equipment->name : 'No equipment',
                    'equipment_image' => $firstItem && $firstItem->equipment ? $firstItem->equipment->image : null,
                    'equipment_category' => $firstItem && $firstItem->equipment ? $firstItem->equipment->category : null,
                    'total_items' => $totalItems,
                    'items_count' => $reservation->items->count(),
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

    /**
     * Request return of issued equipment.
     */
    public function requestReturn(Request $request, Reservation $reservation)
    {
        $user = Auth::user();

        // Check if user owns the reservation
        if ($reservation->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if reservation is currently issued
        if ($reservation->status !== 'issued') {
            return back()->withErrors(['status' => 'Only issued equipment can have return requested.']);
        }

        // Update reservation to indicate return is requested
        $reservation->update([
            'status' => 'return_requested',
            'return_requested_at' => now(),
        ]);

        // Create notification for admin
        Notification::createReturnRequest($reservation);

        return back()->with('success', 'Return request submitted successfully. Please bring the equipment to the lab for processing.');
    }
}
