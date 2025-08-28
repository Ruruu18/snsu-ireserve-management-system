<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(): Response
    {
        $departments = Department::withCount('users')
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Admin/DepartmentList', [
            'departments' => $departments
        ]);
    }

    /**
     * Show the create department page.
     */
    public function create(): Response
    {
        $departments = Department::withCount('users')
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Admin/DepartmentList', [
            'departments' => $departments,
            'showCreateModal' => true
        ]);
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'code' => 'nullable|string|max:10|unique:departments',
            'description' => 'nullable|string|max:1000',
        ]);

        Department::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Department created successfully.');
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'code' => ['nullable', 'string', 'max:10', Rule::unique('departments')->ignore($department->id)],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $department->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        // Check if department has users
        if ($department->users()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete department with existing users.');
        }

        $department->delete();

        return redirect()->back()->with('success', 'Department deleted successfully.');
    }

    /**
     * Get all active departments for dropdowns.
     */
    public function getActiveDepartments()
    {
        $departments = Department::active()->orderBy('name')->get(['id', 'name', 'code']);

        return response()->json($departments);
    }
}
