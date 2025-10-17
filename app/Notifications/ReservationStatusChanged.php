<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reservation;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation, string $oldStatus, string $newStatus)
    {
        $this->reservation = $reservation;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $subject = $this->getEmailSubject();
        $greeting = "Hello {$notifiable->name},";
        $introLines = $this->getIntroLines();
        $actionUrl = $this->getActionUrl();
        $actionText = $this->getActionText();
        $outroLines = $this->getOutroLines();

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($introLines[0]);

        // Add additional intro lines if any
        for ($i = 1; $i < count($introLines); $i++) {
            $mailMessage->line($introLines[$i]);
        }

        // Add action button if applicable
        if ($actionUrl && $actionText) {
            $mailMessage->action($actionText, $actionUrl);
        }

        // Add outro lines
        foreach ($outroLines as $line) {
            $mailMessage->line($line);
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'reservation_code' => $this->reservation->reservation_code,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'student_name' => $this->reservation->user->name,
            'total_items' => $this->reservation->items->sum('quantity'),
            'equipment_names' => $this->reservation->items->take(3)->pluck('equipment.name')->join(', '),
        ];
    }

    /**
     * Get email subject based on status change.
     */
    private function getEmailSubject(): string
    {
        $subjects = [
            'approved' => 'Equipment Reservation Approved',
            'issued' => 'Equipment Issued Successfully',
            'return_requested' => 'Equipment Return Requested',
            'completed' => 'Equipment Reservation Completed',
            'cancelled' => 'Equipment Reservation Cancelled',
            'rejected' => 'Equipment Reservation Rejected',
        ];

        $baseSubject = $subjects[$this->newStatus] ?? 'Reservation Status Updated';
        return $baseSubject . ' - ' . $this->reservation->reservation_code;
    }

    /**
     * Get email intro lines based on status.
     */
    private function getIntroLines(): array
    {
        $equipmentList = $this->getEquipmentList();
        $code = $this->reservation->reservation_code;

        switch ($this->newStatus) {
            case 'approved':
                return [
                    "Great news! Your equipment reservation ({$code}) has been approved.",
                    "Equipment requested: {$equipmentList}",
                    "Your equipment will be prepared for pickup. You'll receive another notification when it's ready for collection."
                ];

            case 'issued':
                return [
                    "Your equipment ({$code}) has been issued and is ready for use!",
                    "Equipment issued: {$equipmentList}",
                    "Please remember to return the equipment by the specified return date."
                ];

            case 'return_requested':
                return [
                    "We've received your return request for reservation {$code}.",
                    "Equipment to return: {$equipmentList}",
                    "Please bring the equipment to the lab for processing. Our staff will verify and process your return."
                ];

            case 'completed':
                return [
                    "Your reservation ({$code}) has been completed successfully!",
                    "Equipment returned: {$equipmentList}",
                    "Thank you for using our equipment responsibly. We hope it served your needs well."
                ];

            case 'cancelled':
            case 'rejected':
                $reason = $this->reservation->admin_notes ? " Reason: {$this->reservation->admin_notes}" : "";
                return [
                    "Your equipment reservation ({$code}) has been {$this->newStatus}.{$reason}",
                    "Equipment requested: {$equipmentList}",
                    "If you have questions about this decision, please contact the lab administrator."
                ];

            default:
                return [
                    "Your reservation ({$code}) status has been updated to: {$this->newStatus}",
                    "Equipment: {$equipmentList}"
                ];
        }
    }

    /**
     * Get action URL based on status.
     */
    private function getActionUrl(): ?string
    {
        switch ($this->newStatus) {
            case 'approved':
            case 'issued':
                return route('student.equipment.issued');
            case 'return_requested':
                return route('student.equipment.issued');
            case 'completed':
                return route('student.equipment.requested');
            default:
                return route('student.dashboard');
        }
    }

    /**
     * Get action button text based on status.
     */
    private function getActionText(): ?string
    {
        switch ($this->newStatus) {
            case 'approved':
                return 'View Approved Reservations';
            case 'issued':
                return 'View Issued Equipment';
            case 'return_requested':
                return 'View Return Status';
            case 'completed':
                return 'View Reservation History';
            default:
                return 'View Dashboard';
        }
    }

    /**
     * Get outro lines.
     */
    private function getOutroLines(): array
    {
        $baseLines = [
            'If you have any questions, please contact the lab administrator.',
            'Thank you for using the SNSU Equipment Reservation System.'
        ];

        if ($this->newStatus === 'issued') {
            array_unshift($baseLines, 'Important: Please handle the equipment with care and return it in good condition.');
        }

        return $baseLines;
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