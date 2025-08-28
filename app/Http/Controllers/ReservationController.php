<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReservationController extends Controller
{
    /**
     * Display a listing of the user's reservations.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $reservations = $user->reservations()
            ->with('equipment')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $reservations->getCollection()->transform(function ($reservation) {
            return [
                'id' => $reservation->id,
                'equipment_name' => $reservation->equipment->name,
                'equipment_id' => $reservation->equipment_id,
                'date' => $reservation->reservation_date->format('M d, Y'),
                'start_time' => $reservation->start_time->format('H:i'),
                'end_time' => $reservation->end_time->format('H:i'),
                'status' => $reservation->status,
                'purpose' => $reservation->purpose,
                'admin_notes' => $reservation->admin_notes,
                'can_cancel' => $reservation->canBeCancelled(),
                'can_edit' => $reservation->status === 'pending',
                'created_at' => $reservation->created_at->format('M d, Y H:i'),
            ];
        });

        return Inertia::render('Student/Reservations', [
            'reservations' => $reservations
        ]);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:500',
        ]);

        // Check if equipment is available
        $equipment = Equipment::findOrFail($validated['equipment_id']);
        if ($equipment->status !== 'available') {
            return back()->withErrors(['equipment_id' => 'This equipment is not available for reservation.']);
        }

        // Check for conflicting reservations
        $conflictingReservation = Reservation::where('equipment_id', $validated['equipment_id'])
            ->where('reservation_date', $validated['reservation_date'])
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($conflictingReservation) {
            return back()->withErrors(['time' => 'This equipment is already reserved for the selected time slot.']);
        }

        // Create the reservation
        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'equipment_id' => $validated['equipment_id'],
            'reservation_date' => $validated['reservation_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'purpose' => $validated['purpose'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Reservation request submitted successfully! You will be notified once it is reviewed.');
    }

    /**
     * Update a reservation.
     */
    public function update(Request $request, Reservation $reservation)
    {
        // Check if user owns the reservation
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow updates for pending reservations
        if ($reservation->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending reservations can be updated.']);
        }

        $validated = $request->validate([
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:500',
        ]);

        // Check for conflicting reservations (excluding current reservation)
        $conflictingReservation = Reservation::where('equipment_id', $reservation->equipment_id)
            ->where('id', '!=', $reservation->id)
            ->where('reservation_date', $validated['reservation_date'])
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($conflictingReservation) {
            return back()->withErrors(['time' => 'This equipment is already reserved for the selected time slot.']);
        }

        $reservation->update($validated);

        return back()->with('success', 'Reservation updated successfully!');
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Reservation $reservation)
    {
        // Check if user owns the reservation
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if reservation can be cancelled
        if (!$reservation->canBeCancelled()) {
            return back()->withErrors(['status' => 'This reservation cannot be cancelled.']);
        }

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reservation cancelled successfully.');
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create(Request $request)
    {
        $equipmentId = $request->get('equipment_id');
        $equipment = null;

        if ($equipmentId) {
            $equipment = Equipment::where('id', $equipmentId)
                ->where('status', 'available')
                ->first();
        }

        $availableEquipment = Equipment::where('status', 'available')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return Inertia::render('Student/CreateReservation', [
            'selectedEquipment' => $equipment,
            'availableEquipment' => $availableEquipment,
        ]);
    }

    /**
     * Get equipment availability for a specific date.
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'date' => 'required|date',
        ]);

        $reservations = Reservation::where('equipment_id', $validated['equipment_id'])
            ->where('reservation_date', $validated['date'])
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->select('start_time', 'end_time')
            ->get()
            ->map(function ($reservation) {
                return [
                    'start' => $reservation->start_time->format('H:i'),
                    'end' => $reservation->end_time->format('H:i'),
                ];
            });

        return response()->json([
            'reserved_slots' => $reservations
        ]);
    }
}
