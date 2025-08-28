<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
        public function __invoke(Request $request): RedirectResponse
    {
        // Find the user by ID from the URL
        $user = User::findOrFail($request->route('id'));

        // Verify the hash matches the user's email
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid verification link.']);
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Your email is already verified. You can now log in with your credentials.');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Redirect to login with success message instead of auto-login
        return redirect()->route('login')->with('status', 'Email verified successfully! You can now log in with your credentials.');
    }
}
