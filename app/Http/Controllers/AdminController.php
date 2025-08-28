<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
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

        // Get issued equipment count (approved reservations that haven't been returned)
        $issuedEquipment = Reservation::where('status', 'approved')
            ->whereNull('returned_at')
            ->count();

        // Get total members (students + faculty/staff + admins)
        $totalMembers = User::count();

        // Get recent reservations for the dashboard
        $recentReservations = Reservation::with(['user', 'equipment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'name' => $reservation->equipment->name ?? 'Unknown Equipment',
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
                'members' => $totalMembers,
            ],
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

        return Inertia::render('Admin/IssueEquipment', [
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

        return Inertia::render('Admin/IssuedEquipment', [
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

        return Inertia::render('Admin/RequestedEquipment', [
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

        $reservation->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Reservation approved successfully.');
    }

    /**
     * Reject a reservation request.
     */
    public function rejectReservation(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending reservations can be rejected.']);
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $reservation->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Reservation rejected successfully.');
    }

    /**
     * Mark equipment as returned.
     */
    public function returnEquipment(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'approved' || $reservation->returned_at) {
            return back()->withErrors(['status' => 'Only approved, unreturned reservations can be marked as returned.']);
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $reservation->update([
            'status' => 'completed',
            'returned_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Equipment marked as returned successfully.');
    }
}
