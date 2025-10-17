<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{


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
                $firstItem = $reservation->items->first();
                return [
                    'id' => $reservation->id,
                    'name' => $firstItem ? $firstItem->equipment->name : 'Multiple items',
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
                $firstItem = $reservation->items->first();
                return [
                    'id' => $reservation->id,
                    'name' => $firstItem ? $firstItem->equipment->name : 'Multiple items',
                    'user' => $reservation->user->name ?? 'Unknown User',
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
     * Display the student list page.
     */
    public function students(): Response
    {
        $students = User::where('role', 'student')
            ->with('department')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $departments = Department::active()->orderBy('name')->get();

        return Inertia::render('Admin/StudentList', [
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

        return Inertia::render('Admin/StudentList', [
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

        // Load department relationship for notification
        $user->load('department');

        // Create notification for student creation
        Notification::createStudentCreation($user);

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
     * Display all reservations for management.
     */
    public function reservations(): Response
    {
        $reservations = Reservation::with(['user', 'items.equipment'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Reservations', [
            'reservations' => $reservations,
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

        // Broadcast status update

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

        // Broadcast status update

        return back()->with('success', 'Reservation cancelled successfully.');
    }

    /**
     * Mark equipment as returned.
     */
    public function returnEquipment(Request $request, Reservation $reservation)
    {
        if (!in_array($reservation->status, ['issued', 'return_requested'])) {
            return back()->withErrors(['status' => 'Only issued reservations can be marked as returned.']);
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

        // Broadcast status update

        return back()->with('success', 'Equipment marked as returned successfully.');
    }
}
