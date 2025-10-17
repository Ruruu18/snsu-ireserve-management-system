<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Zxing\QrReader;

class QRScannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/QRScanner', [
            'recentScans' => $this->getRecentScans(),
        ]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            // Decode QR data
            $qrData = json_decode($request->qr_data, true);

            if (!$qrData || !isset($qrData['code'])) {
                return Inertia::render('Admin/QRScanner', [
                    'recentScans' => $this->getRecentScans(),
                    'errors' => [
                        'message' => 'Invalid QR code format.'
                    ]
                ]);
            }

            // Verify signature (using minimal format)
            $expectedSignature = substr(hash('sha256', $qrData['code'] . config('app.key')), 0, 16);
            if (!isset($qrData['sig']) || $qrData['sig'] !== $expectedSignature) {
                return Inertia::render('Admin/QRScanner', [
                    'recentScans' => $this->getRecentScans(),
                    'errors' => [
                        'message' => 'QR code signature is invalid. This may be a forged code.'
                    ]
                ]);
            }

            // Find the reservation
            $reservation = Reservation::with(['user', 'items.equipment'])
                ->where('reservation_code', $qrData['code'])
                ->first();

            if (!$reservation) {
                return Inertia::render('Admin/QRScanner', [
                    'recentScans' => $this->getRecentScans(),
                    'errors' => [
                        'message' => 'Reservation not found.'
                    ]
                ]);
            }

            // Auto-process reservations based on status
            $message = 'QR code scanned successfully!';

            if ($reservation->status === 'pending') {
                // Auto-approve and issue pending reservations
                $approveResult = $this->autoApproveAndIssueReservation($reservation);
                $message = $approveResult['message'];
                $reservation = $approveResult['reservation'];
            } elseif ($reservation->status === 'approved') {
                // Auto-issue approved reservations
                $issueResult = $this->autoIssueReservation($reservation);
                $message = $issueResult['message'];
                $reservation = $issueResult['reservation'];
            } elseif ($reservation->status === 'issued' || $reservation->status === 'return_requested') {
                // Auto-return issued equipment or process return request
                $returnResult = $this->autoReturnEquipment($reservation);
                $message = $returnResult['message'];
                $reservation = $returnResult['reservation'];
            } elseif ($reservation->status === 'completed') {
                $message = 'This reservation has already been completed and returned.';
            } else {
                $message = 'Reservation status: ' . $reservation->status . '. No action taken.';
            }

            return Inertia::render('Admin/QRScanner', [
                'recentScans' => $this->getRecentScans(),
                'reservation' => $this->formatReservation($reservation),
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('QR scan failed: ' . $e->getMessage());

            return Inertia::render('Admin/QRScanner', [
                'recentScans' => $this->getRecentScans(),
                'errors' => [
                    'message' => 'Failed to process QR code. Please try again.'
                ]
            ]);
        }
    }

    public function uploadQR(Request $request)
    {
        $request->validate([
            'qr_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Remove SVG for now
        ]);

        try {
            $image = $request->file('qr_image');

            // Store the uploaded image
            $imagePath = $image->store('qr-uploads', 'public');
            $fullPath = Storage::disk('public')->path($imagePath);

            // Log the upload attempt
            Log::info('QR upload attempt', ['file' => $fullPath, 'size' => filesize($fullPath)]);

            // Try to decode QR from the uploaded image
            try {
                $qrReader = new QrReader($fullPath);
                $qrData = $qrReader->text();

                Log::info('QR decode result', ['data' => $qrData]);

                if ($qrData) {
                    // Decode the QR data (should be JSON)
                    $decodedData = json_decode($qrData, true);

                    Log::info('JSON decode result', ['decoded' => $decodedData]);

                    // Handle both old and new QR formats
                    $reservationCode = null;
                    $isValidSignature = false;

                    if ($decodedData && isset($decodedData['code'])) {
                        // New minimal format
                        $reservationCode = $decodedData['code'];
                        $expectedSignature = substr(hash('sha256', $reservationCode . config('app.key')), 0, 16);
                        $isValidSignature = isset($decodedData['sig']) && $decodedData['sig'] === $expectedSignature;

                        Log::info('New format signature check', [
                            'expected' => $expectedSignature,
                            'received' => $decodedData['sig'] ?? 'none'
                        ]);
                    } elseif ($decodedData && isset($decodedData['reservation_code'])) {
                        // Old format (backward compatibility)
                        $reservationCode = $decodedData['reservation_code'];
                        $expectedSignature = hash('sha256', $reservationCode . config('app.key'));
                        $isValidSignature = isset($decodedData['signature']) && $decodedData['signature'] === $expectedSignature;

                        Log::info('Old format signature check', [
                            'expected' => $expectedSignature,
                            'received' => $decodedData['signature'] ?? 'none'
                        ]);
                    }

                    if ($reservationCode && $isValidSignature) {
                        // Find the reservation
                        $reservation = Reservation::with(['user', 'items.equipment'])
                            ->where('reservation_code', $reservationCode)
                            ->first();

                        if ($reservation) {
                            // Auto-process reservations based on status
                            $message = 'QR code decoded and reservation found successfully!';

                            if ($reservation->status === 'pending') {
                                // Auto-approve and issue pending reservations
                                $approveResult = $this->autoApproveAndIssueReservation($reservation);
                                $message = $approveResult['message'];
                                $reservation = $approveResult['reservation'];
                            } elseif ($reservation->status === 'approved') {
                                // Auto-issue approved reservations
                                $issueResult = $this->autoIssueReservation($reservation);
                                $message = $issueResult['message'];
                                $reservation = $issueResult['reservation'];
                            } elseif ($reservation->status === 'issued' || $reservation->status === 'return_requested') {
                                // Auto-return issued equipment or process return request
                                $returnResult = $this->autoReturnEquipment($reservation);
                                $message = $returnResult['message'];
                                $reservation = $returnResult['reservation'];
                            } elseif ($reservation->status === 'returned') {
                                $message = 'This reservation has already been completed and returned.';
                            } else {
                                $message = 'Reservation status: ' . $reservation->status . '. No action taken.';
                            }

                            return Inertia::render('Admin/QRScanner', [
                                'recentScans' => $this->getRecentScans(),
                                'reservation' => $this->formatReservation($reservation),
                                'message' => $message,
                                'uploaded_qr' => [
                                    'path' => $imagePath,
                                    'url' => asset('storage/' . $imagePath),
                                    'decoded_data' => $qrData
                                ]
                            ]);
                        } else {
                                return Inertia::render('Admin/QRScanner', [
                                    'recentScans' => $this->getRecentScans(),
                                    'errors' => [
                                        'message' => 'QR code is valid but reservation not found: ' . $reservationCode
                                    ],
                                    'uploaded_qr' => [
                                        'path' => $imagePath,
                                        'url' => asset('storage/' . $imagePath),
                                        'decoded_data' => $qrData
                                    ]
                                ]);
                            }
                    } else {
                        $errorMessage = 'QR code signature is invalid';
                        if ($reservationCode) {
                            $errorMessage .= ' for reservation: ' . $reservationCode;
                        } else {
                            $errorMessage .= '. Invalid QR format or missing reservation code.';
                        }

                        return Inertia::render('Admin/QRScanner', [
                            'recentScans' => $this->getRecentScans(),
                            'errors' => [
                                'message' => $errorMessage
                            ],
                            'uploaded_qr' => [
                                'path' => $imagePath,
                                'url' => asset('storage/' . $imagePath),
                                'decoded_data' => $qrData
                            ]
                        ]);
                    }
                } else {
                    return Inertia::render('Admin/QRScanner', [
                        'recentScans' => $this->getRecentScans(),
                        'errors' => [
                            'message' => 'Could not decode QR code from the uploaded image. Please try with a PNG or JPG image.'
                        ],
                        'uploaded_qr' => [
                            'path' => $imagePath,
                            'url' => asset('storage/' . $imagePath),
                            'message' => 'Image uploaded but QR code could not be decoded.'
                        ]
                    ]);
                }
            } catch (\Exception $qrException) {
                Log::error('QR decode failed: ' . $qrException->getMessage());

                return Inertia::render('Admin/QRScanner', [
                    'recentScans' => $this->getRecentScans(),
                    'errors' => [
                        'message' => 'Failed to decode QR code: ' . $qrException->getMessage() . '. Please ensure the image contains a valid QR code in PNG or JPG format.'
                    ],
                    'uploaded_qr' => [
                        'path' => $imagePath,
                        'url' => asset('storage/' . $imagePath),
                        'message' => 'Image uploaded but QR code could not be decoded.'
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('QR upload failed: ' . $e->getMessage());

            return Inertia::render('Admin/QRScanner', [
                'recentScans' => $this->getRecentScans(),
                'errors' => [
                    'message' => 'Failed to upload QR image: ' . $e->getMessage()
                ]
            ]);
        }
    }

    public function issueEquipment(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:reservation_items,id',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $admin = Auth::user();
                $reservation = Reservation::with(['items.equipment', 'user'])->findOrFail($request->reservation_id);

                // Issue selected items
                $issuedItems = [];
                foreach ($request->item_ids as $itemId) {
                    $item = ReservationItem::findOrFail($itemId);

                    if ($item->reservation_id !== $reservation->id) {
                        continue;
                    }

                    $item->update([
                        'status' => 'issued',
                        'issued_at' => now(),
                    ]);

                    $issuedItems[] = $item;
                }

                // Check if all items are issued
                $allItemsIssued = $reservation->items()->where('status', '!=', 'issued')->count() === 0;

                if ($allItemsIssued) {
                    $reservation->update([
                        'status' => 'issued',
                        'issued_at' => now(),
                        'issued_by' => $admin->id,
                    ]);
                }

                return back()->with([
                    'reservation' => $this->formatReservation($reservation->fresh(['items.equipment', 'user'])),
                    'message' => count($issuedItems) . ' item(s) issued successfully.'
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Equipment issue failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to issue equipment. Please try again.',
            ], 500);
        }
    }

    public function returnEquipment(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:reservation_items,id',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $admin = Auth::user();
                $reservation = Reservation::with(['items.equipment', 'user'])->findOrFail($request->reservation_id);

                // Return selected items
                $returnedItems = [];
                foreach ($request->item_ids as $itemId) {
                    $item = ReservationItem::findOrFail($itemId);

                    if ($item->reservation_id !== $reservation->id || $item->status !== 'issued') {
                        continue;
                    }

                    $item->update([
                        'status' => 'returned',
                        'returned_at' => now(),
                    ]);

                    $returnedItems[] = $item;
                }

                // Check if all items are returned
                $allItemsReturned = $reservation->items()->where('status', '!=', 'returned')->count() === 0;

                if ($allItemsReturned) {
                    $reservation->update([
                        'status' => 'returned',
                        'returned_at' => now(),
                        'returned_by' => $admin->id,
                    ]);
                }

                return back()->with([
                    'reservation' => $this->formatReservation($reservation->fresh(['items.equipment', 'user'])),
                    'message' => count($returnedItems) . ' item(s) returned successfully.'
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Equipment return failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process return. Please try again.',
            ], 500);
        }
    }

    private function formatReservation($reservation)
    {
        return [
            'id' => $reservation->id,
            'reservation_code' => $reservation->reservation_code,
            'status' => $reservation->status,
            'student' => [
                'id' => $reservation->user->id,
                'name' => $reservation->user->name,
                'email' => $reservation->user->email,
            ],
            'purpose' => $reservation->purpose,
            'date' => $reservation->reservation_date->toDateString(),
            'start_time' => $reservation->start_time->format('g:i A'),
            'end_time' => $reservation->end_time->format('g:i A'),
            'notes' => $reservation->notes,
            'issued_at' => $reservation->issued_at?->format('Y-m-d H:i:s'),
            'returned_at' => $reservation->returned_at?->format('Y-m-d H:i:s'),
            'items' => $reservation->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'equipment_id' => $item->equipment_id,
                    'equipment_name' => $item->equipment->name,
                    'equipment_image' => $item->equipment->image,
                    'quantity' => $item->quantity,
                    'status' => $item->status,
                    'issued_at' => $item->issued_at?->format('Y-m-d H:i:s'),
                    'returned_at' => $item->returned_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total_items' => $reservation->items->count(),
            'issued_items' => $reservation->items->where('status', 'issued')->count(),
            'returned_items' => $reservation->items->where('status', 'returned')->count(),
        ];
    }

    private function autoApproveAndIssueReservation($reservation)
    {
        try {
            return DB::transaction(function () use ($reservation) {
                $admin = Auth::user();

                // First approve the reservation
                $reservation->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $admin->id,
                ]);

                // Then issue all items automatically
                $issuedCount = 0;
                foreach ($reservation->items as $item) {
                    $item->update([
                        'status' => 'issued',
                        'issued_at' => now(),
                    ]);
                    $issuedCount++;
                }

                // Update reservation status to issued
                $reservation->update([
                    'status' => 'issued',
                    'issued_at' => now(),
                    'issued_by' => $admin->id,
                ]);

                $refreshedReservation = $reservation->fresh(['items.equipment', 'user']);

                return [
                    'reservation' => $refreshedReservation,
                    'message' => "Reservation approved and equipment issued automatically! {$issuedCount} item(s) have been issued to {$reservation->user->name}."
                ];
            });
        } catch (\Exception $e) {
            Log::error('Auto-approve and issue failed: ' . $e->getMessage());
            return [
                'reservation' => $reservation,
                'message' => 'QR code found but failed to auto-process reservation. Please process manually.'
            ];
        }
    }

    private function autoIssueReservation($reservation)
    {
        try {
            return DB::transaction(function () use ($reservation) {
                $admin = Auth::user();

                // Issue all pending items automatically
                $issuedCount = 0;
                foreach ($reservation->items as $item) {
                    if ($item->status === 'pending') {
                        $item->update([
                            'status' => 'issued',
                            'issued_at' => now(),
                        ]);
                        $issuedCount++;
                    }
                }

                // Update reservation status to issued
                $reservation->update([
                    'status' => 'issued',
                    'issued_at' => now(),
                    'issued_by' => $admin->id,
                ]);

                $refreshedReservation = $reservation->fresh(['items.equipment', 'user']);

                return [
                    'reservation' => $refreshedReservation,
                    'message' => "Equipment issued automatically! {$issuedCount} item(s) have been issued to {$reservation->user->name}."
                ];
            });
        } catch (\Exception $e) {
            Log::error('Auto-issue failed: ' . $e->getMessage());
            return [
                'reservation' => $reservation,
                'message' => 'QR code found but failed to auto-issue equipment. Please issue manually.'
            ];
        }
    }

    private function autoReturnEquipment($reservation)
    {
        try {
            return DB::transaction(function () use ($reservation) {
                $admin = Auth::user();

                // Return all issued items automatically
                $returnedCount = 0;
                foreach ($reservation->items as $item) {
                    if ($item->status === 'issued') {
                        $item->update([
                            'status' => 'returned',
                            'returned_at' => now(),
                        ]);
                        $returnedCount++;
                    }
                }

                // Update reservation status to completed
                $reservation->update([
                    'status' => 'completed',
                    'returned_at' => now(),
                    'returned_by' => $admin->id,
                ]);

                $refreshedReservation = $reservation->fresh(['items.equipment', 'user']);

                return [
                    'reservation' => $refreshedReservation,
                    'message' => "Equipment returned successfully! {$returnedCount} item(s) have been returned by {$reservation->user->name}. Thank you!"
                ];
            });
        } catch (\Exception $e) {
            Log::error('Auto-return failed: ' . $e->getMessage());
            return [
                'reservation' => $reservation,
                'message' => 'QR code found but failed to auto-return equipment. Please process return manually.'
            ];
        }
    }

    private function getRecentScans()
    {
        return Reservation::with(['user', 'items.equipment'])
            ->whereNotNull('issued_at')
            ->orderBy('issued_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($reservation) {
                return $this->formatReservation($reservation);
            });
    }
}
