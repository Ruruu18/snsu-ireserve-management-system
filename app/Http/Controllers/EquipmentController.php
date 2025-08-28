<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the equipment.
     */
    public function index()
    {
        $equipment = Equipment::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/EquipmentList', [
            'equipment' => $equipment
        ]);
    }

    /**
     * Show the create equipment page.
     */
    public function create()
    {
        $equipment = Equipment::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/EquipmentList', [
            'equipment' => $equipment,
            'showAddModal' => true
        ]);
    }

    /**
     * Store a newly created equipment in storage.
     */
    public function store(Request $request)
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

        Equipment::create($validated);

        return redirect()->back()->with('success', 'Equipment added successfully!');
    }

    /**
     * Update the specified equipment in storage.
     */
    public function update(Request $request, Equipment $equipment)
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
                Rule::unique('equipment')->ignore($equipment->id),
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($equipment->image && Storage::disk('public')->exists($equipment->image)) {
                Storage::disk('public')->delete($equipment->image);
            }

            $imagePath = $request->file('image')->store('equipment-images', 'public');
            $validated['image'] = $imagePath;
        }

        $equipment->update($validated);

        return redirect()->back()->with('success', 'Equipment updated successfully!');
    }

    /**
     * Remove the specified equipment from storage.
     */
    public function destroy(Equipment $equipment)
    {
        // Check if equipment has active reservations
        $hasActiveReservations = $equipment->reservations()
            ->whereIn('status', ['pending', 'approved'])
            ->where('reservation_date', '>=', now()->toDateString())
            ->exists();

        if ($hasActiveReservations) {
            return redirect()->back()->with('error', 'Cannot delete equipment with active reservations.');
        }

        // Delete image if exists
        if ($equipment->image && Storage::disk('public')->exists($equipment->image)) {
            Storage::disk('public')->delete($equipment->image);
        }

        $equipment->delete();

        return redirect()->back()->with('success', 'Equipment deleted successfully!');
    }

    /**
     * Get equipment data for API calls
     */
    public function getEquipment()
    {
        $equipment = Equipment::select(['id', 'name', 'category', 'status', 'image'])
            ->where('status', 'available')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json($equipment);
    }

    /**
     * Get equipment by category for dropdown
     */
    public function getByCategory($category = null)
    {
        $query = Equipment::where('status', 'available');

        if ($category) {
            $query->where('category', $category);
        }

        $equipment = $query->select(['id', 'name', 'category', 'image'])
            ->orderBy('name')
            ->get();

        return response()->json($equipment);
    }
}
