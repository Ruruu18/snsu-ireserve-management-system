<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\User;
use App\Notifications\NewReservationReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            ->with('items.equipment')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $reservations->getCollection()->transform(function ($reservation) {
            $firstItem = $reservation->items->first();
            $totalItems = $reservation->items->sum('quantity');

            return [
                'id' => $reservation->id,
                'equipment_name' => $firstItem ? $firstItem->equipment->name : 'Multiple items',
                'equipment_id' => $firstItem ? $firstItem->equipment->id : null,
                'total_items' => $totalItems,
                'items_count' => $reservation->items->count(),
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

        // Get statistics for sidebar
        $stats = [
            'total_reservations' => $user->reservations()->count(),
            'pending_reservations' => $user->reservations()->where('status', 'pending')->count(),
            'active_reservations' => $user->reservations()->whereIn('status', ['approved', 'issued'])->count(),
            'completed_reservations' => $user->reservations()->where('status', 'completed')->count(),
        ];

        return Inertia::render('Student/Reservations', [
            'reservations' => $reservations,
            'stats' => $stats
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

        try {
            return DB::transaction(function () use ($validated) {
                // Check if equipment is available
                $equipment = Equipment::findOrFail($validated['equipment_id']);
                if ($equipment->status !== 'available') {
                    throw ValidationException::withMessages([
                        'equipment_id' => 'This equipment is not available for reservation.'
                    ]);
                }

                // Check for conflicting reservations using the new cart system
                $conflictingReservation = ReservationItem::where('equipment_id', $validated['equipment_id'])
                    ->whereHas('reservation', function ($query) use ($validated) {
                        $query->where('reservation_date', $validated['reservation_date'])
                            ->where('status', '!=', 'cancelled')
                            ->where(function ($q) use ($validated) {
                                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                                  ->orWhere(function ($q2) use ($validated) {
                                      $q2->where('start_time', '<=', $validated['start_time'])
                                         ->where('end_time', '>=', $validated['end_time']);
                                  });
                            });
                    })
                    ->exists();

                if ($conflictingReservation) {
                    throw ValidationException::withMessages([
                        'time' => 'This equipment is already reserved for the selected time slot.'
                    ]);
                }

                // Create the reservation using the new cart system
                $reservation = Reservation::create([
                    'user_id' => Auth::id(),
                    'reservation_date' => $validated['reservation_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'purpose' => $validated['purpose'],
                    'status' => 'pending',
                ]);

                // Create the reservation item
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'equipment_id' => $validated['equipment_id'],
                    'quantity' => 1,
                    'status' => 'pending',
                ]);

                // Load the reservation with relationships
                $reservation->load(['items.equipment', 'user']);

                // Generate QR code
                $qrData = $reservation->generateQRData();
                $qrCodeDataString = json_encode($qrData);

                // Generate QR code as PNG using chillerlan library (works with GD)
                $options = new \chillerlan\QRCode\QROptions([
                    'version'    => 10,
                    'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel'   => \chillerlan\QRCode\QRCode::ECC_L,
                    'scale'      => 8,
                    'imageBase64' => false,
                ]);

                $qrcode = new \chillerlan\QRCode\QRCode($options);
                $qrCodePng = $qrcode->render($qrCodeDataString);

                // Save PNG QR code to storage
                $qrFileName = 'qr-codes/reservation-' . $reservation->id . '.png';
                Storage::disk('public')->put($qrFileName, $qrCodePng);

                // Update reservation with QR data
                $reservation->update([
                    'qr_code_data' => $qrData,
                    'qr_code_path' => $qrFileName,
                ]);

                // Create notification for admin
                Notification::createNewReservation($reservation);

                // Send email notifications to all admins and faculty
                $admins = User::whereIn('role', ['admin', 'faculty'])->get();
                foreach ($admins as $admin) {
                    $admin->notify(new NewReservationReceived($reservation));
                }

                return back()->with('success', 'Reservation request submitted successfully! You will be notified once it is reviewed.');
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Reservation creation failed: ' . $e->getMessage());

            throw ValidationException::withMessages([
                'general' => 'Failed to process your reservation. Please try again.',
            ]);
        }
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
        // Get the equipment IDs from this reservation's items
        $equipmentIds = $reservation->items->pluck('equipment_id');

        $conflictingReservation = ReservationItem::whereIn('equipment_id', $equipmentIds)
            ->whereHas('reservation', function ($query) use ($validated, $reservation) {
                $query->where('id', '!=', $reservation->id)
                    ->where('reservation_date', $validated['reservation_date'])
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($validated) {
                        $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhere(function ($q2) use ($validated) {
                              $q2->where('start_time', '<=', $validated['start_time'])
                                 ->where('end_time', '>=', $validated['end_time']);
                          });
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

        $oldStatus = $reservation->status;

        $reservation->update(['status' => 'cancelled']);

        // Broadcast status update for email notification

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

        $reservations = ReservationItem::where('equipment_id', $validated['equipment_id'])
            ->whereHas('reservation', function ($query) use ($validated) {
                $query->where('reservation_date', $validated['date'])
                    ->where('status', '!=', 'cancelled');
            })
            ->with('reservation:id,start_time,end_time')
            ->get()
            ->map(function ($item) {
                return [
                    'start' => $item->reservation->start_time->format('H:i'),
                    'end' => $item->reservation->end_time->format('H:i'),
                ];
            });

        return response()->json([
            'reserved_slots' => $reservations
        ]);
    }
}
