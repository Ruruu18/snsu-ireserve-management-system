<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Add item to cart
     */
    public function add(Request $request, Equipment $equipment)
    {
        $quantity = $request->input('quantity', 1);

        // Validate quantity
        if ($quantity < 1 || $quantity > $equipment->quantity) {
            return back()->withErrors(['quantity' => 'Invalid quantity']);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$equipment->id])) {
            $cart[$equipment->id]['quantity'] += $quantity;
        } else {
            $cart[$equipment->id] = [
                'id' => $equipment->id,
                'quantity' => $quantity,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Item added to cart!');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, Equipment $equipment)
    {
        $quantity = $request->input('quantity', 1);

        if ($quantity < 1) {
            return $this->remove($equipment);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$equipment->id])) {
            $cart[$equipment->id]['quantity'] = min($quantity, $equipment->quantity);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Cart updated!');
    }

    /**
     * Remove item from cart
     */
    public function remove(Equipment $equipment)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$equipment->id])) {
            unset($cart[$equipment->id]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Item removed from cart!');
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Cart cleared!');
    }

    public function checkout(Request $request)
    {
        // For GET requests, we'll pass empty cartItems since the frontend will handle cart state
        // The frontend cart state will be used to populate the checkout form
        return Inertia::render('Student/CartCheckout', [
            'cartItems' => [], // Frontend will populate this from localStorage
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'purpose' => 'required|string|max:500',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:equipment,id',
            'items.*.quantity' => 'required|integer|min:1|max:10',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $user = Auth::user();

                // Create the reservation
                $reservation = Reservation::create([
                    'user_id' => $user->id,
                    'purpose' => $request->purpose,
                    'reservation_date' => $request->date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'notes' => $request->notes,
                    'status' => 'pending',
                ]);

                // Create reservation items
                foreach ($request->items as $item) {
                    ReservationItem::create([
                        'reservation_id' => $reservation->id,
                        'equipment_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'status' => 'pending',
                    ]);
                }

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

                // Convert PNG to base64 data URL for immediate display
                $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodePng);

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

                // QR code base64 already set above

                return Inertia::render('Student/ReservationQR', [
                    'reservation' => [
                        'id' => $reservation->id,
                        'student_name' => $user->name,
                        'purpose' => $reservation->purpose,
                        'date' => $reservation->reservation_date->format('M d, Y'),
                        'start_time' => $reservation->start_time->format('g:i A'),
                        'end_time' => $reservation->end_time->format('g:i A'),
                        'notes' => $reservation->notes,
                        'status' => $reservation->status,
                        'items' => $reservation->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'equipment_id' => $item->equipment_id,
                                'equipment_name' => $item->equipment->name,
                                'equipment_image' => $item->equipment->image,
                                'quantity' => $item->quantity,
                                'status' => $item->status,
                            ];
                        }),
                    ],
                    'qrCode' => $qrCodeBase64,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Cart processing failed: ' . $e->getMessage());

            throw ValidationException::withMessages([
                'general' => 'Failed to process your reservation. Please try again.',
            ]);
        }
    }

    public function viewQR(Request $request, Reservation $reservation)
    {
        // Ensure user can only view their own reservations
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to reservation');
        }

        // Load reservation with items
        $reservation->load(['items.equipment', 'user']);

        // Check if QR code exists
        if (!$reservation->qr_code_data || !$reservation->qr_code_path) {
            return redirect()->back()->withErrors([
                'message' => 'QR code not available for this reservation.'
            ]);
        }

        // Get QR code - check if file exists, if not regenerate
        $qrCodeBase64 = null;
        if (Storage::disk('public')->exists($reservation->qr_code_path)) {
            $qrCodeContent = Storage::disk('public')->get($reservation->qr_code_path);
            // Determine file type based on path extension
            $fileExtension = pathinfo($reservation->qr_code_path, PATHINFO_EXTENSION);
            if ($fileExtension === 'svg') {
                $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCodeContent);
            } else {
                $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodeContent);
            }
        } else {
            // Regenerate QR code if file doesn't exist (use PNG with chillerlan library)
            $qrData = $reservation->generateQRData();
            $qrCodeDataString = json_encode($qrData);

            $options = new \chillerlan\QRCode\QROptions([
                'version'    => 10,
                'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => \chillerlan\QRCode\QRCode::ECC_L,
                'scale'      => 8,
                'imageBase64' => false,
            ]);

            $qrcode = new \chillerlan\QRCode\QRCode($options);
            $qrCodePng = $qrcode->render($qrCodeDataString);

            // Update path to PNG and save regenerated QR code
            $newQrPath = 'qr-codes/reservation-' . $reservation->id . '.png';
            Storage::disk('public')->put($newQrPath, $qrCodePng);

            // Update reservation with new path
            $reservation->update(['qr_code_path' => $newQrPath]);

            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodePng);
        }

        return Inertia::render('Student/ViewQR', [
            'reservation' => [
                'id' => $reservation->id,
                'reservation_code' => $reservation->reservation_code,
                'student_name' => $reservation->user->name,
                'purpose' => $reservation->purpose,
                'date' => $reservation->reservation_date->format('M d, Y'),
                'start_time' => $reservation->start_time->format('g:i A'),
                'end_time' => $reservation->end_time->format('g:i A'),
                'notes' => $reservation->notes,
                'status' => $reservation->status,
                'created_at' => $reservation->created_at->format('M d, Y g:i A'),
                'issued_at' => $reservation->issued_at ? $reservation->issued_at->format('M d, Y g:i A') : null,
                'returned_at' => $reservation->returned_at ? $reservation->returned_at->format('M d, Y g:i A') : null,
                'items' => $reservation->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'equipment_id' => $item->equipment_id,
                        'equipment_name' => $item->equipment->name,
                        'equipment_image' => $item->equipment->image,
                        'quantity' => $item->quantity,
                        'status' => $item->status,
                        'issued_at' => $item->issued_at ? $item->issued_at->format('M d, Y g:i A') : null,
                        'returned_at' => $item->returned_at ? $item->returned_at->format('M d, Y g:i A') : null,
                    ];
                }),
                'total_items' => $reservation->items->sum('quantity'),
                'items_count' => $reservation->items->count(),
            ],
            'qrCode' => $qrCodeBase64,
        ]);
    }
}
