<?php

namespace App\Events;

use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationStatusChanged;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reservation;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Reservation $reservation, string $oldStatus, string $newStatus)
    {
        $this->reservation = $reservation;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;

        // Send email notification to the student immediately (not queued)
        $this->reservation->user->notifyNow(new ReservationStatusChanged($reservation, $oldStatus, $newStatus));
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->reservation->user_id),
            new PrivateChannel('admin'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'reservation' => [
                'id' => $this->reservation->id,
                'reservation_code' => $this->reservation->reservation_code,
                'status' => $this->newStatus,
                'old_status' => $this->oldStatus,
                'student_name' => $this->reservation->user->name,
                'updated_at' => $this->reservation->updated_at->toISOString(),
            ],
            'message' => $this->getStatusMessage(),
        ];
    }

    /**
     * Get the event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'reservation.status.updated';
    }

    /**
     * Get status change message.
     */
    private function getStatusMessage(): string
    {
        $messages = [
            'pending' => 'Your reservation is being reviewed.',
            'approved' => 'Your reservation has been approved! Please wait for equipment to be issued.',
            'issued' => 'Your equipment has been issued! Please check your issued equipment page.',
            'return_requested' => 'Return request received. Please bring equipment to the lab.',
            'completed' => 'Your reservation has been completed. Thank you!',
            'cancelled' => 'Your reservation has been cancelled.',
            'rejected' => 'Your reservation has been rejected. Please check admin notes for details.',
        ];

        return $messages[$this->newStatus] ?? 'Your reservation status has been updated.';
    }
}