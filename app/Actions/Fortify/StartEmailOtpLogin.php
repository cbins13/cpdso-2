<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;

class StartEmailOtpLogin
{
    public function __construct(
        private readonly LoginRateLimiter $limiter,
        private readonly EmailOtpService $emailOtpService,
    ) {}

    public function handle($request, $next)
    {
        if ($this->limiter->tooManyAttempts($request)) {
            event(new Lockout($request));

            $seconds = $this->limiter->availableIn($request);

            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }

        $email = $request->input(Fortify::username());
        $user = User::where(Fortify::username(), $email)->first();

        if (! $user || ! is_string($user->password) || ! Hash::check($request->password, $user->password)) {
            $this->limiter->increment($request);

            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.failed')],
            ]);
        }

        $this->limiter->clear($request);

        $this->emailOtpService->issue(
            user: $user,
            purpose: 'login',
            action: 'issue',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $request->session()->put([
            'auth_otp.pending_user_id' => $user->id,
            'auth_otp.pending_remember' => $request->boolean('remember'),
            'auth_otp.pending_purpose' => 'login',
            'auth_otp.pending_email' => $user->email,
        ]);

        return $next($request);
    }
}
