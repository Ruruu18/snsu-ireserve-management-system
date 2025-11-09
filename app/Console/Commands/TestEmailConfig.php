<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class TestEmailConfig extends Command
{
    protected $signature = 'email:test-config {email?}';
    protected $description = 'Test email configuration and send a test email';

    public function handle()
    {
        $this->info('=== EMAIL CONFIGURATION TEST ===');
        $this->newLine();

        // Check mail configuration
        $this->info('Mail Settings:');
        $this->line('  Driver: ' . config('mail.default'));
        $this->line('  Host: ' . config('mail.mailers.smtp.host'));
        $this->line('  Port: ' . config('mail.mailers.smtp.port'));
        $this->line('  Username: ' . config('mail.mailers.smtp.username'));
        $this->line('  Password: ' . (config('mail.mailers.smtp.password') ? '****' . substr(config('mail.mailers.smtp.password'), -4) : 'NOT SET'));
        $this->line('  Encryption: ' . config('mail.mailers.smtp.encryption'));
        $this->line('  From Address: ' . config('mail.from.address'));
        $this->line('  From Name: ' . config('mail.from.name'));
        $this->newLine();

        // Check if email argument provided
        $email = $this->argument('email');
        if (!$email) {
            $email = $this->ask('Enter email address to send test to');
        }

        if (!$email) {
            $this->error('No email provided!');
            return 1;
        }

        $this->info("Sending test email to: {$email}");
        $this->newLine();

        try {
            Mail::raw('This is a test email from SNSU iReserve. If you receive this, your email configuration is working correctly!', function($message) use ($email) {
                $message->to($email)
                        ->subject('SNSU iReserve - Email Configuration Test');
            });

            $this->info('✓ Email sent successfully!');
            $this->line('Check the inbox (and spam folder) for: ' . $email);
            return 0;

        } catch (\Exception $e) {
            $this->error('✗ Failed to send email!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();

            $this->warn('Troubleshooting steps:');
            $this->line('1. Verify .env file has correct SMTP settings');
            $this->line('2. Run: php artisan config:clear');
            $this->line('3. Check if port 587 is open (firewall)');
            $this->line('4. Verify Gmail app password is correct');

            return 1;
        }
    }
}
