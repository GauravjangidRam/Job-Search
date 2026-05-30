<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    /**
     * Generate a 6-digit numeric OTP for the user.
     *
     * Stores the hashed OTP on the user, sets expiration to 10 minutes from now,
     * and resets the attempt counter. Returns the plain-text OTP for sending via email.
     */
    public function generate(User $user): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->otp = Hash::make($otp);
        $user->otp_expires_at = now()->addMinutes(10);
        $user->otp_attempts = 0;
        $user->save();

        return $otp;
    }

    /**
     * Verify the submitted OTP against the stored hash.
     *
     * Returns true if the OTP matches. On failure, increments the attempt counter.
     */
    public function verify(User $user, string $otp): bool
    {
        if (Hash::check($otp, $user->otp)) {
            return true;
        }

        $user->otp_attempts = $user->otp_attempts + 1;
        $user->save();

        return false;
    }

    /**
     * Invalidate the current OTP by clearing all OTP-related fields.
     */
    public function invalidate(User $user): void
    {
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->otp_attempts = 0;
        $user->save();
    }

    /**
     * Check if the user's OTP has expired.
     *
     * Returns true if otp_expires_at is null or in the past.
     */
    public function isExpired(User $user): bool
    {
        if ($user->otp_expires_at === null) {
            return true;
        }

        return $user->otp_expires_at->isPast();
    }
}
