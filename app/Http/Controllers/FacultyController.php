<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Reservation;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Zxing\QrReader;

class FacultyController extends Controller
{
    /**
     * Get dashboard statistics for the faculty dashboard.
     */
    public function getDashboardStats()
    {
        // Get total equipment count
        $totalEquipment = Equipment::count();

        // Get requested equipment count (pending reservations)
        $requestedEquipment = Reservation::where('status', 'pending')->count();

        // Get issued equipment count (issued reservations that haven't been returned)
        $issuedEquipment = Reservation::where('status', 'issued')
            ->whereNull('returned_at')
            ->count();

        // Get total students only
        $totalStudents = User::where('role', 'student')->count();

        // Get recent reservations for the dashboard
        $recentReservations = Reservation::with(['user', 'items.equipment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($reservation) {
                $firstItem = $reservation->items->first();
                $equipmentName = $firstItem ? $firstItem->equipment->name : 'Unknown Equipment';
                if ($reservation->items->count() > 1) {
                    $equipmentName .= ' + ' . ($reservation->items->count() - 1) . ' more';
                }

                return [
                    'id' => $reservation->id,
                    'name' => $equipmentName,
                    'user' => $reservation->user->name ?? 'Unknown User',
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
                'students' => $totalStudents,
            ],
            'recent_reservations' => $recentReservations
        ]);
    }

    /**
     * Display all reservations for faculty management.
     */
    public function reservationsIndex()
    {
        $reservations = Reservation::with(['user', 'items.equipment'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $reservations->getCollection()->transform(function ($reservation) {
            $firstItem = $reservation->items->first();
            $totalItems = $reservation->items->sum('quantity');

            return [
                'id' => $reservation->id,
                'equipment_name' => $firstItem ? $firstItem->equipment->name : 'Multiple items',
                'total_items' => $totalItems,
                'items_count' => $reservation->items->count(),
                'user_name' => $reservation->user->name,
                'user_email' => $reservation->user->email,
                'date' => $reservation->reservation_date->format('M d, Y'),
                'start_time' => $reservation->start_time->format('g:i A'),
                'end_time' => $reservation->end_time->format('g:i A'),
                'status' => $reservation->status,
                'purpose' => $reservation->purpose,
                'admin_notes' => $reservation->admin_notes,
                'created_at' => $reservation->created_at->format('M d, Y H:i'),
            ];
        });

        return Inertia::render('Faculty/ReservationManagement', [
            'reservations' => $reservations
        ]);
    }

    /**
     * Display the student list page.
     */
    public function students(): Response
    {
        $students = User::where('role', 'student')
            ->with('department')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $departments = Department::active()->orderBy('name')->get();

        return Inertia::render('Faculty/StudentList', [
            'students' => $students,
            'departments' => $departments
        ]);
    }

    /**
     * Show the create student page.
     */
    public function createStudent(): Response
    {
        $departments = Department::active()->orderBy('name')->get();

        return Inertia::render('Faculty/StudentList', [
            'students' => User::where('role', 'student')->with('department')->orderBy('created_at', 'desc')->paginate(10),
            'departments' => $departments,
            'showCreateModal' => true
        ]);
    }

    /**
     * Store a newly created student.
     */
    public function storeStudent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'department_id' => $request->department_id,
            // Remove email_verified_at to require verification
        ]);

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        return redirect()->back()->with('success', 'Student created successfully. Verification email sent.');
    }

    /**
     * Update the specified student.
     */
    public function updateStudent(Request $request, User $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student->id)],
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $student->update([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
        ]);

        return redirect()->back()->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student.
     */
    public function destroyStudent(User $student)
    {
        $student->delete();

        return redirect()->back()->with('success', 'Student deleted successfully.');
    }

    /**
     * Get students for AJAX requests (search, pagination).
     */
    public function getStudents(Request $request)
    {
        $query = User::where('role', 'student');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $students = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json($students);
    }

    /**
     * Display the issue equipment page (pending reservations).
     */
    public function issueEquipment(): Response
    {
        $pendingReservations = Reservation::with(['user', 'equipment'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Transform to include computed properties and format times
        $pendingReservations->getCollection()->transform(function ($reservation) {
            $reservation->duration = $reservation->getDurationAttribute();
            $reservation->formatted_start_time = \Carbon\Carbon::parse($reservation->start_time)->format('g:i A');
            $reservation->formatted_end_time = \Carbon\Carbon::parse($reservation->end_time)->format('g:i A');
            $reservation->formatted_date = $reservation->reservation_date->format('M d, Y');
            return $reservation;
        });

        return Inertia::render('Faculty/IssueEquipment', [
            'reservations' => $pendingReservations
        ]);
    }

    /**
     * Display the issued equipment page (approved/active reservations).
     */
    public function issuedEquipment(): Response
    {
        $issuedReservations = Reservation::with(['user', 'equipment'])
            ->where('status', 'approved')
            ->whereNull('returned_at')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Faculty/IssuedEquipment', [
            'reservations' => $issuedReservations
        ]);
    }

    /**
     * Display the requested equipment page (all reservations).
     */
    public function requestedEquipment(): Response
    {
        $allReservations = Reservation::with(['user', 'equipment'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Transform to include computed properties
        $allReservations->getCollection()->transform(function ($reservation) {
            $reservation->duration = $reservation->getDurationAttribute();
            return $reservation;
        });

        return Inertia::render('Faculty/RequestedEquipment', [
            'reservations' => $allReservations
        ]);
    }

    /**
     * Approve a reservation request.
     */
    public function approveReservation(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending reservations can be approved.']);
        }

        $oldStatus = $reservation->status;

        $reservation->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Broadcast status update and send email notification

        return back()->with('success', 'Reservation approved successfully.');
    }

    /**
     * Reject a reservation request.
     */
    public function rejectReservation(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending reservations can be cancelled.']);
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $reservation->status;

        $reservation->update([
            'status' => 'cancelled',
            'admin_notes' => $request->admin_notes,
        ]);

        // Broadcast status update and send email notification

        return back()->with('success', 'Reservation cancelled successfully.');
    }

    /**
     * Issue equipment for an approved reservation.
     */
    public function issueReservation(Reservation $reservation)
    {
        if ($reservation->status !== 'approved') {
            return back()->withErrors(['status' => 'Only approved reservations can be issued.']);
        }

        $oldStatus = $reservation->status;

        $reservation->update([
            'status' => 'issued',
            'issued_at' => now(),
            'issued_by' => auth()->id(),
        ]);

        // Broadcast status update and send email notification

        return back()->with('success', 'Equipment issued successfully.');
    }

    /**
     * Mark equipment as returned.
     */
    public function returnEquipment(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'issued' || $reservation->returned_at) {
            return back()->withErrors(['status' => 'Only issued, unreturned reservations can be marked as returned.']);
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $reservation->status;

        $reservation->update([
            'status' => 'completed',
            'returned_at' => now(),
            'returned_by' => auth()->id(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Broadcast status update and send email notification

        return back()->with('success', 'Equipment marked as returned successfully.');
    }

        /**
     * Display equipment list page.
     */
    public function equipmentIndex()
    {
        $equipment = \App\Models\Equipment::orderBy('created_at', 'desc')->get();

        return Inertia::render('Faculty/EquipmentList', [
            'equipment' => $equipment
        ]);
    }

    /**
     * Show create equipment page.
     */
    public function equipmentCreate()
    {
        return Inertia::render('Faculty/EquipmentList', [
            'equipment' => \App\Models\Equipment::orderBy('created_at', 'desc')->get(),
            'showAddModal' => true
        ]);
    }

    /**
     * Store new equipment.
     */
    public function equipmentStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'status' => 'required|in:available,unavailable,maintenance',
            'location' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:equipment',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('equipment-images', 'public');
            $validated['image'] = $imagePath;
        }

        \App\Models\Equipment::create($validated);

        return redirect()->back()->with('success', 'Equipment created successfully.');
    }

    /**
     * Update equipment.
     */
    public function equipmentUpdate(Request $request, \App\Models\Equipment $equipment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'status' => 'required|in:available,unavailable,maintenance',
            'location' => 'nullable|string|max:255',
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('equipment')->ignore($equipment->id),
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($equipment->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($equipment->image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($equipment->image);
            }

            $imagePath = $request->file('image')->store('equipment-images', 'public');
            $validated['image'] = $imagePath;
        }

        $equipment->update($validated);

        return redirect()->back()->with('success', 'Equipment updated successfully.');
    }

    /**
     * Delete equipment.
     */
    public function equipmentDestroy(\App\Models\Equipment $equipment)
    {
        $equipment->delete();
        return redirect()->back()->with('success', 'Equipment deleted successfully.');
    }

    /**
     * Get equipment data for AJAX.
     */
    public function getEquipment(Request $request)
    {
        $query = \App\Models\Equipment::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $equipment = $query->orderBy('created_at', 'desc')->get();

        return response()->json($equipment);
    }

    /**
     * Get equipment by category.
     */
    public function getEquipmentByCategory($category = null)
    {
        $query = \App\Models\Equipment::query();

        if ($category) {
            $query->where('category', $category);
        }

        $equipment = $query->orderBy('created_at', 'desc')->get();

        return response()->json($equipment);
    }

    /**
     * Display QR scanner page for faculty.
     */
    public function qrScanner()
    {
        return Inertia::render('Faculty/QRScanner', [
            'recentScans' => $this->getRecentScans(),
        ]);
    }

    /**
     * Handle QR code scanning for faculty.
     */
    public function qrScan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            // Decode QR data
            $qrData = json_decode($request->qr_data, true);

            if (!$qrData || !isset($qrData['code'])) {
                return Inertia::render('Faculty/QRScanner', [
                    'recentScans' => $this->getRecentScans(),
                    'errors' => [
                        'message' => 'Invalid QR code format.'
                    ]
                ]);
            }

            // Verify signature (using minimal format)
            $expectedSignature = substr(hash('sha256', $qrData['code'] . config('app.key')), 0, 16);
            if (!isset($qrData['sig']) || $qrData['sig'] !== $expectedSignature) {
                return Inertia::render('Faculty/QRScanner', [
                    'recentScans' => $this->getRecentScans(),
                    'errors' => [
                        'message' => 'QR code signature is invalid. This may be a forged code.'
                    ]
                ]);
            }

            // Find reservation
            $reservation = Reservation::where('reservation_code', $qrData['code'])->first();

            if (!$reservation) {
                return Inertia::render('Faculty/QRScanner', [
                    'recentScans' => $this->getRecentScans(),
                    'errors' => [
                        'message' => 'Reservation not found for this QR code.'
                    ]
                ]);
            }

            // Format the reservation data properly
            $reservation->load(['user', 'items.equipment']);
            $formattedReservation = [
                'id' => $reservation->id,
                'reservation_code' => $reservation->reservation_code,
                'user' => [
                    'name' => $reservation->user->name,
                    'email' => $reservation->user->email,
                ],
                'status' => $reservation->status,
                'reservation_date' => $reservation->reservation_date->format('M d, Y'),
                'start_time' => $reservation->start_time->format('g:i A'),
                'end_time' => $reservation->end_time->format('g:i A'),
                'purpose' => $reservation->purpose,
                'items' => $reservation->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'equipment' => [
                            'name' => $item->equipment->name,
                        ],
                    ];
                }),
            ];

            return Inertia::render('Faculty/QRScanner', [
                'recentScans' => $this->getRecentScans(),
                'reservation' => $formattedReservation,
                'message' => 'QR code scanned successfully!'
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Faculty/QRScanner', [
                'recentScans' => $this->getRecentScans(),
                'errors' => [
                    'message' => 'Error processing QR code: ' . $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Handle QR code image upload for faculty.
     */
    public function qrUpload(Request $request)
    {
        $request->validate([
            'qr_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $image = $request->file('qr_image');
            $path = $image->store('qr-uploads', 'public');
            $fullPath = storage_path('app/public/' . $path);

            // Use QrReader to decode the image
            $qrcode = new QrReader($fullPath);
            $qrData = $qrcode->text();

            // Clean up uploaded file
            \Storage::disk('public')->delete($path);

            if (!$qrData) {
                return Inertia::render('Faculty/QRScanner', [
                    'recentScans' => $this->getRecentScans(),
                    'errors' => [
                        'message' => 'No QR code found in the uploaded image.'
                    ]
                ]);
            }

            // Process the QR data using the same logic as scan method
            return $this->qrScan(new Request(['qr_data' => $qrData]));

        } catch (\Exception $e) {
            return Inertia::render('Faculty/QRScanner', [
                'recentScans' => $this->getRecentScans(),
                'errors' => [
                    'message' => 'Error processing uploaded image: ' . $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Get recent QR scans for display.
     */
    private function getRecentScans()
    {
        return Reservation::with(['user', 'items.equipment'])
            ->whereNotNull('approved_at')
            ->orWhereNotNull('returned_at')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($reservation) {
                $firstItem = $reservation->items->first();
                return [
                    'id' => $reservation->id,
                    'code' => $reservation->reservation_code,
                    'user' => $reservation->user->name,
                    'equipment' => $firstItem ? $firstItem->equipment->name : 'Multiple items',
                    'status' => $reservation->status,
                    'date' => $reservation->updated_at->format('M d, Y H:i'),
                    'action' => $reservation->returned_at ? 'returned' : 'issued',
                ];
            });
    }
}
