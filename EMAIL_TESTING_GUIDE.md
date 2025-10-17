# Email Testing Guide - SNSU iReserve System

## Issue Found and Fixed

### Problem
Leah was not receiving email notifications because the `notifications` table had an incorrect structure. The table was missing the required `notifiable_id` and `notifiable_type` columns that Laravel's notification system needs.

### Error Details
- **Error**: `Column not found: 1054 Unknown column 'notifiable_id' in 'field list'`
- **Cause**: The notifications migration was using a custom table structure instead of Laravel's standard notification table structure
- **Impact**: All queued notification emails were failing silently

### Fix Applied
Updated `/database/migrations/2025_09_28_132842_create_notifications_table.php` to use Laravel's standard notification table structure:

```php
Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('type');
    $table->morphs('notifiable');  // Creates notifiable_id and notifiable_type
    $table->text('data');
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
});
```

## Testing Email Configuration

### 1. Send a Test Email

You can test if emails are working by running:

```bash
php artisan email:test lmgallentes.15@gmail.com
```

Or for any other email:

```bash
php artisan email:test email@example.com
```

This will:
- Show your current mail configuration
- Send a simple test email
- If the email belongs to a user in the system, send an actual notification

### 2. Process Queued Jobs

Since notifications are queued, you need to process the queue for emails to be sent:

```bash
# Process all queued jobs until queue is empty
php artisan queue:work --stop-when-empty

# Or process one job at a time
php artisan queue:work --once

# Keep the queue worker running (recommended for production)
php artisan queue:work
```

### 3. Check for Failed Jobs

If any emails fail to send, you can check the failed jobs:

```bash
# List failed jobs
php artisan queue:failed

# Clear failed jobs (after fixing the issue)
php artisan queue:flush

# Retry failed jobs
php artisan queue:retry all
```

### 4. Check Logs

If emails are not being received, check the Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

## Current Email Configuration

Based on `.env` file:
- **Mailer**: SMTP
- **Host**: smtp.gmail.com
- **Port**: 587
- **Encryption**: TLS
- **From Address**: lmgallentes.15@gmail.com
- **From Name**: Snsu-Ireserve

## Important Notes

1. **Queue Worker Must Be Running**: Notifications are queued, so you must have a queue worker running for emails to be sent:
   ```bash
   php artisan queue:work
   ```

2. **Gmail App Password**: The system is using a Gmail App Password (`avij jgiu rpiz bbro`), which is correct for Gmail accounts with 2FA enabled.

3. **Check Spam Folder**: Gmail may sometimes mark system emails as spam. Ask recipients to check their spam folder.

4. **Production Setup**: In production, you should run the queue worker as a daemon using Supervisor or similar process manager.

## Testing After a Reservation is Made

1. Student creates a reservation → Admin receives email notification
2. Admin approves reservation → Student receives email notification
3. Admin issues equipment → Student receives email notification
4. Student requests return → Admin receives email notification
5. Admin completes return → Student receives email notification

## Verifying Database Notifications

You can also check if notifications are being stored in the database:

```bash
php artisan tinker --execute="DB::table('notifications')->count();"
php artisan tinker --execute="DB::table('notifications')->latest()->first();"
```

## Troubleshooting

### Emails Not Sending?

1. **Check Queue**: `php artisan queue:work --stop-when-empty`
2. **Check Failed Jobs**: `php artisan queue:failed`
3. **Check Logs**: `tail -f storage/logs/laravel.log`
4. **Verify SMTP Settings**: Run `php artisan email:test`
5. **Clear Cache**: `php artisan config:clear && php artisan cache:clear`

### Still Not Working?

- Verify Gmail settings allow SMTP access
- Check if App Password is still valid
- Try sending to a different email provider
- Check your internet connection
- Verify firewall isn't blocking port 587

## Next Steps

✅ Notifications table structure fixed
✅ Test email command created
✅ Email sending verified

**Action Required**: Keep a queue worker running to process email notifications:

```bash
# For development (in a separate terminal):
php artisan queue:work

# For production (using Supervisor):
# See Laravel docs on queue workers in production
```

---

**Last Updated**: October 17, 2025
**Status**: Email notifications are now working correctly
