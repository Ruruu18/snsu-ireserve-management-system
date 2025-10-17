<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReservationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->reservation->user->name;
        $reservationCode = $this->reservation->reservation_code;
        $equipmentList = $this->getEquipmentList();
        $reservationDate = $this->reservation->reservation_date->format('M d, Y');
        $timeSlot = $this->reservation->start_time->format('g:i A') . ' - ' . $this->reservation->end_time->format('g:i A');

        return (new MailMessage)
            ->subject("New Equipment Reservation - {$reservationCode}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new equipment reservation has been submitted and requires your review.")
            ->line("**Student:** {$studentName}")
            ->line("**Reservation Code:** {$reservationCode}")
            ->line("**Equipment Requested:** {$equipmentList}")
            ->line("**Date:** {$reservationDate}")
            ->line("**Time:** {$timeSlot}")
            ->line("**Purpose:** {$this->reservation->purpose}")
            ->action('Review Reservation', route('admin.reservations.index'))
            ->line('Please review and approve/reject this reservation as soon as possible.')
            ->line('Thank you for managing the SNSU Equipment Reservation System.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'reservation_code' => $this->reservation->reservation_code,
            'student_name' => $this->reservation->user->name,
            'student_email' => $this->reservation->user->email,
            'total_items' => $this->reservation->items->sum('quantity'),
            'equipment_names' => $this->reservation->items->take(3)->pluck('equipment.name')->join(', '),
            'reservation_date' => $this->reservation->reservation_date->format('Y-m-d'),
            'purpose' => $this->reservation->purpose,
        ];
    }

    /**
     * Get formatted equipment list for display.
     */
    private function getEquipmentList(): string
    {
        $items = $this->reservation->items;
        $totalItems = $items->sum('quantity');

        if ($items->count() <= 3) {
            return $items->map(function ($item) {
                return "{$item->equipment->name} (Qty: {$item->quantity})";
            })->join(', ');
        } else {
            $firstThree = $items->take(3)->map(function ($item) {
                return "{$item->equipment->name} (Qty: {$item->quantity})";
            })->join(', ');

            $remaining = $items->count() - 3;
            return "{$firstThree} and {$remaining} more items (Total: {$totalItems} items)";
        }
    }
}