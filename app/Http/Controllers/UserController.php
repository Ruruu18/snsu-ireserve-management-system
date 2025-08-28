<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display the user management page.
     */
    public function index(Request $request): Response
    {
        $query = User::where('role', '!=', 'admin')
            ->with('department')
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Apply role filter
        if ($request->role && in_array($request->role, ['student', 'faculty_staff'])) {
            $query->where('role', $request->role);
        }



        $users = $query->paginate($request->per_page ?? 10);
        $departments = Department::active()->orderBy('name')->get();

        return Inertia::render('Admin/UserList', [
            'users' => $users,
            'departments' => $departments,
            'filters' => $request->only(['search', 'role', 'per_page'])
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,faculty_staff',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department_id' => $request->department_id,
            'email_verified_at' => now(),
        ]);

        return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $request->role)) . ' created successfully.');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        // Prevent updating admin users
        if ($user->isAdmin()) {
            return redirect()->back()->with('error', 'Cannot modify admin users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:student,faculty_staff',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department_id' => $request->department_id,
        ]);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting admin users
        if ($user->isAdmin()) {
            return redirect()->back()->with('error', 'Cannot delete admin users.');
        }

        // Check if user has active reservations
        if ($user->activeReservations()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete user with active reservations.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    /**
     * Promote a student to faculty staff.
     */
    public function promoteToFacultyStaff(User $user)
    {
        if (!$user->isStudent()) {
            return redirect()->back()->with('error', 'Only students can be promoted to faculty staff.');
        }

        $user->update(['role' => 'faculty_staff']);

        return redirect()->back()->with('success', $user->name . ' has been promoted to faculty staff.');
    }

    /**
     * Demote faculty staff to student.
     */
    public function demoteToStudent(User $user)
    {
        if (!$user->isFacultyStaff()) {
            return redirect()->back()->with('error', 'Only faculty staff can be demoted to student.');
        }

        $user->update(['role' => 'student']);

        return redirect()->back()->with('success', $user->name . ' has been demoted to student.');
    }

    /**
     * Get users for AJAX requests (search, pagination, filtering).
     */
    public function getData(Request $request)
    {
        $query = User::where('role', '!=', 'admin')
            ->with('department')
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Apply role filter
        if ($request->role && in_array($request->role, ['student', 'faculty_staff'])) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate($request->per_page ?? 10);

        return response()->json($users);
    }

    /**
     * Get user statistics.
     */
    public function getStats()
    {
        $stats = [
            'total_users' => User::where('role', '!=', 'admin')->count(),
            'students' => User::where('role', 'student')->count(),
            'faculty_staff' => User::where('role', 'faculty_staff')->count(),
            'users_with_departments' => User::where('role', '!=', 'admin')->whereNotNull('department_id')->count(),
            'users_without_departments' => User::where('role', '!=', 'admin')->whereNull('department_id')->count(),
        ];

        return response()->json($stats);
    }
}
