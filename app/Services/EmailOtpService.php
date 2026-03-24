<?php

namespace App\Services;

use App\Mail\EmailOtpCodeMail;
use App\Models\EmailOtp;
use App\Models\OtpAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class EmailOtpService
{
    private const CODE_LENGTH = 6;

    private const EXPIRES_IN_MINUTES = 10;

    private const MAX_FAILED_ATTEMPTS = 3;

    private const RESEND_COOLDOWN_SECONDS = 60;

    private const MAX_RESENDS_PER_HOUR = 5;

    public function issue(
        User $user,
        string $purpose,
        string $action = 'issue',
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $meta = null,
    ): void
    {
        $otp = EmailOtp::firstOrNew([
            'user_id' => $user->id,
            'purpose' => $purpose,
        ]);

        $now = now();

        if ($otp->exists) {
            if ($otp->last_sent_at && $otp->last_sent_at->diffInSeconds($now) < self::RESEND_COOLDOWN_SECONDS) {
                $this->audit(
                    user: $user,
                    purpose: $purpose,
                    action: $action,
                    status: 'failed',
                    reason: 'cooldown',
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                    meta: $meta,
                );

                throw ValidationException::withMessages([
                    'code' => ['Please wait at least 60 seconds before requesting another code.'],
                ]);
            }

            if ($otp->resend_window_started_at && $otp->resend_window_started_at->diffInMinutes($now) >= 60) {
                $otp->resend_window_started_at = $now;
                $otp->resend_count = 0;
            }

            if ($otp->resend_count >= self::MAX_RESENDS_PER_HOUR) {
                $this->audit(
                    user: $user,
                    purpose: $purpose,
                    action: $action,
                    status: 'failed',
                    reason: 'resend_limit',
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                    meta: $meta,
                );

                throw ValidationException::withMessages([
                    'code' => ['You have reached the resend limit. Try again later.'],
                ]);
            }
        } else {
            $otp->resend_window_started_at = $now;
            $otp->resend_count = 0;
        }

        $code = str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);

        $otp->fill([
            'code_hash' => Hash::make($code),
            'expires_at' => $now->copy()->addMinutes(self::EXPIRES_IN_MINUTES),
            'attempts' => 0,
            'last_sent_at' => $now,
            'resend_count' => ($otp->resend_count ?? 0) + 1,
        ]);

        $otp->save();

        try {
            Mail::to($user->email)->send(new EmailOtpCodeMail($user, $code, $purpose));
        } catch (Throwable $exception) {
            $otp->delete();

            $this->audit(
                user: $user,
                purpose: $purpose,
                action: $action,
                status: 'failed',
                reason: 'delivery_failed',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                meta: [
                    ...($meta ?? []),
                    'error' => $exception->getMessage(),
                ],
            );

            throw ValidationException::withMessages([
                'code' => ['Unable to send the verification email right now. Please try again.'],
            ]);
        }

        $this->audit(
            user: $user,
            purpose: $purpose,
            action: $action,
            status: 'success',
            reason: null,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            meta: $meta,
        );
    }

    public function verify(
        User $user,
        string $purpose,
        string $inputCode,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $meta = null,
    ): void
    {
        $otp = EmailOtp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->first();

        if (! $otp) {
            $this->audit(
                user: $user,
                purpose: $purpose,
                action: 'verify',
                status: 'failed',
                reason: 'missing',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                meta: $meta,
            );

            throw ValidationException::withMessages([
                'code' => ['No active verification code was found. Request a new one.'],
            ]);
        }

        if ($otp->expires_at->isPast()) {
            $this->audit(
                user: $user,
                purpose: $purpose,
                action: 'verify',
                status: 'failed',
                reason: 'expired',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                meta: $meta,
            );

            throw ValidationException::withMessages([
                'code' => ['This verification code has expired. Request a new one.'],
            ]);
        }

        if ($otp->attempts >= self::MAX_FAILED_ATTEMPTS) {
            $this->audit(
                user: $user,
                purpose: $purpose,
                action: 'verify',
                status: 'failed',
                reason: 'attempts_exceeded',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                meta: $meta,
            );

            throw ValidationException::withMessages([
                'code' => ['Too many failed attempts. Request a new code.'],
            ]);
        }

        if (! Hash::check($inputCode, $otp->code_hash)) {
            $otp->increment('attempts');

            $this->audit(
                user: $user,
                purpose: $purpose,
                action: 'verify',
                status: 'failed',
                reason: 'invalid_code',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                meta: [
                    ...($meta ?? []),
                    'attempts' => $otp->fresh()->attempts,
                ],
            );

            throw ValidationException::withMessages([
                'code' => ['The verification code is incorrect.'],
            ]);
        }

        $otp->delete();

        $this->audit(
            user: $user,
            purpose: $purpose,
            action: 'verify',
            status: 'success',
            reason: null,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            meta: $meta,
        );
    }

    private function audit(
        User $user,
        string $purpose,
        string $action,
        string $status,
        ?string $reason,
        ?string $ipAddress,
        ?string $userAgent,
        ?array $meta,
    ): void {
        OtpAuditLog::create([
            'user_id' => $user->id,
            'purpose' => $purpose,
            'action' => $action,
            'status' => $status,
            'reason' => $reason,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'meta' => $meta,
        ]);
    }
}
