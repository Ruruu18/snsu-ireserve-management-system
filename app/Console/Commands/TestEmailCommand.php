<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Reservation;
use App\Notifications\ReservationStatusChanged;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email? : The email address to send to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify email configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? 'lmgallentes.15@gmail.com';

        $this->info("Sending test email to: {$email}");
        $this->info("Mail configuration:");
        $this->info("  Mailer: " . config('mail.default'));
        $this->info("  Host: " . config('mail.mailers.smtp.host'));
        $this->info("  Port: " . config('mail.mailers.smtp.port'));
        $this->info("  From: " . config('mail.from.address'));
        $this->newLine();

        try {
            // Simple test email
            Mail::raw('This is a test email from SNSU iReserve System. If you receive this, email notifications are working correctly!', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email - SNSU iReserve System');
            });

            $this->info('✓ Simple test email sent successfully!');
            $this->newLine();

            // Test with actual notification if user exists
            $user = User::where('email', $email)->first();

            if ($user) {
                $this->info('Testing with actual notification...');

                // Get the latest reservation for testing
                $reservation = Reservation::with('items.equipment', 'user')
                    ->where('user_id', $user->id)
                    ->latest()
                    ->first();

                if ($reservation) {
                    $user->notify(new ReservationStatusChanged(
                        $reservation,
                        'pending',
                        'approved'
                    ));

                    $this->info('✓ Test notification sent successfully!');
                    $this->info("  Reservation: {$reservation->reservation_code}");
                } else {
                    $this->warn('No reservations found for this user to test with.');
                }
            } else {
                $this->warn("No user found with email: {$email}");
                $this->info('Only simple test email was sent.');
            }

            $this->newLine();
            $this->info('Email test completed! Check the inbox for: ' . $email);
            $this->info('Note: Check spam folder if email is not in inbox.');

        } catch (\Exception $e) {
            $this->error('✗ Failed to send test email!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->info('Troubleshooting tips:');
            $this->info('1. Check your .env file has correct MAIL_* settings');
            $this->info('2. Verify MAIL_USERNAME and MAIL_PASSWORD are correct');
            $this->info('3. For Gmail, ensure you are using an App Password, not your regular password');
            $this->info('4. Check if the account allows "Less secure app access" or 2FA with App Password');
            $this->info('5. Run: php artisan config:clear && php artisan cache:clear');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
