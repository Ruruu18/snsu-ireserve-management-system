<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\User;
use App\Models\Notification;
use App\Events\ReservationStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReservationService
{
    /**
     * Create a new reservation with cart items.
     */
    public function createReservation(User $user, array $data): Reservation
    {
        return DB::transaction(function () use ($user, $data) {
            Log::info('Creating reservation', [
                'user_id' => $user->id,
                'items_count' => count($data['cart_items'])
            ]);

            // Create reservation
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'reservation_date' => $data['reservation_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'purpose' => $data['purpose'],
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            // Create reservation items
            foreach ($data['cart_items'] as $item) {
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'equipment_id' => $item['equipment_id'],
                    'quantity' => $item['quantity'],
                    'status' => 'pending',
                ]);
            }

            // Generate QR code
            $this->generateQRCode($reservation);

            // Clear caches
            $this->clearReservationCaches($user->id);

            // Create notification for admins
            Notification::createNewReservation($reservation->fresh(['items.equipment']));

            Log::info('Reservation created successfully', ['reservation_id' => $reservation->id]);

            return $reservation->fresh(['items.equipment']);
        });
    }

    /**
     * Approve a reservation.
     */
    public function approveReservation(Reservation $reservation, User $admin): Reservation
    {
        return DB::transaction(function () use ($reservation, $admin) {
            Log::info('Approving reservation', [
                'reservation_id' => $reservation->id,
                'admin_id' => $admin->id
            ]);

            $oldStatus = $reservation->status;

            $reservation->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]);

            broadcast(new ReservationStatusUpdated($reservation, $oldStatus, 'approved'));

            $this->clearReservationCaches($reservation->user_id);

            return $reservation;
        });
    }

    /**
     * Reject a reservation.
     */
    public function rejectReservation(Reservation $reservation, User $admin, ?string $reason = null): Reservation
    {
        return DB::transaction(function () use ($reservation, $admin, $reason) {
            Log::info('Rejecting reservation', [
                'reservation_id' => $reservation->id,
                'admin_id' => $admin->id
            ]);

            $oldStatus = $reservation->status;

            $reservation->update([
                'status' => 'cancelled',
                'admin_notes' => $reason,
            ]);

            broadcast(new ReservationStatusUpdated($reservation, $oldStatus, 'cancelled'));

            $this->clearReservationCaches($reservation->user_id);

            return $reservation;
        });
    }

    /**
     * Issue equipment for a reservation.
     */
    public function issueEquipment(Reservation $reservation, User $admin, ?array $itemIds = null): Reservation
    {
        return DB::transaction(function () use ($reservation, $admin, $itemIds) {
            Log::info('Issuing equipment', [
                'reservation_id' => $reservation->id,
                'admin_id' => $admin->id
            ]);

            $items = $itemIds
                ? $reservation->items()->whereIn('id', $itemIds)->get()
                : $reservation->items;

            foreach ($items as $item) {
                $item->update([
                    'status' => 'issued',
                    'issued_at' => now(),
                ]);
            }

            // Check if all items are issued
            if ($reservation->items()->where('status', '!=', 'issued')->count() === 0) {
                $reservation->update([
                    'status' => 'issued',
                    'issued_at' => now(),
                    'issued_by' => $admin->id,
                ]);

                broadcast(new ReservationStatusUpdated($reservation, 'approved', 'issued'));
            }

            $this->clearReservationCaches($reservation->user_id);

            return $reservation->fresh(['items.equipment']);
        });
    }

    /**
     * Process equipment return.
     */
    public function returnEquipment(Reservation $reservation, User $admin, ?array $itemIds = null): Reservation
    {
        return DB::transaction(function () use ($reservation, $admin, $itemIds) {
            Log::info('Processing return', [
                'reservation_id' => $reservation->id,
                'admin_id' => $admin->id
            ]);

            $items = $itemIds
                ? $reservation->items()->whereIn('id', $itemIds)->get()
                : $reservation->items;

            foreach ($items as $item) {
                if ($item->status === 'issued') {
                    $item->update([
                        'status' => 'returned',
                        'returned_at' => now(),
                    ]);
                }
            }

            // Check if all items are returned
            if ($reservation->items()->where('status', '!=', 'returned')->count() === 0) {
                $oldStatus = $reservation->status;

                $reservation->update([
                    'status' => 'completed',
                    'returned_at' => now(),
                    'returned_by' => $admin->id,
                ]);

                broadcast(new ReservationStatusUpdated($reservation, $oldStatus, 'completed'));
            }

            $this->clearReservationCaches($reservation->user_id);

            return $reservation->fresh(['items.equipment']);
        });
    }

    /**
     * Request return for issued equipment.
     */
    public function requestReturn(Reservation $reservation): Reservation
    {
        Log::info('Return requested', ['reservation_id' => $reservation->id]);

        $reservation->update([
            'status' => 'return_requested',
            'return_requested_at' => now(),
        ]);

        // Create notification
        Notification::createReturnRequest($reservation);

        $this->clearReservationCaches($reservation->user_id);

        return $reservation;
    }

    /**
     * Generate QR code for reservation.
     */
    private function generateQRCode(Reservation $reservation): void
    {
        $qrData = $reservation->generateQRData();
        $qrCodeData = json_encode($qrData);

        $qrCodePath = 'qrcodes/' . $reservation->reservation_code . '.png';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate QR code
        QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrCodeData, $fullPath);

        // Update reservation with QR data
        $reservation->update([
            'qr_code_data' => $qrData,
            'qr_code_path' => $qrCodePath,
        ]);
    }

    /**
     * Clear reservation-related caches.
     */
    private function clearReservationCaches(int $userId): void
    {
        Cache::forget("user_{$userId}_reservations");
        Cache::forget("user_{$userId}_active_reservations");
        Cache::forget('reservation_statistics');
        Cache::forget('dashboard_stats');
    }

    /**
     * Get reservation statistics with caching.
     */
    public function getStatistics(): array
    {
        return Cache::remember('reservation_statistics', 300, function () {
            return [
                'total' => Reservation::count(),
                'pending' => Reservation::where('status', 'pending')->count(),
                'approved' => Reservation::where('status', 'approved')->count(),
                'issued' => Reservation::where('status', 'issued')->count(),
                'completed' => Reservation::where('status', 'completed')->count(),
                'cancelled' => Reservation::where('status', 'cancelled')->count(),
                'today' => Reservation::whereDate('created_at', today())->count(),
                'this_week' => Reservation::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'this_month' => Reservation::whereMonth('created_at', now()->month)->count(),
            ];
        });
    }

    /**
     * Get user's active reservations count.
     */
    public function getUserActiveReservationsCount(int $userId): int
    {
        return Cache::remember("user_{$userId}_active_reservations", 300, function () use ($userId) {
            return Reservation::where('user_id', $userId)
                ->whereIn('status', ['pending', 'approved', 'issued', 'return_requested'])
                ->count();
        });
    }
}