<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EmailOtpController extends Controller
{
    public function challenge(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('auth_otp.pending_user_id')) {
            return to_route('login');
        }

        return Inertia::render('auth/OtpChallenge', [
            'email' => (string) $request->session()->get('auth_otp.pending_email', ''),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function verifyPending(Request $request, EmailOtpService $emailOtpService): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $userId = $request->session()->get('auth_otp.pending_user_id');
        $remember = (bool) $request->session()->get('auth_otp.pending_remember', false);

        if (! $userId) {
            return to_route('login');
        }

        $user = User::findOrFail($userId);

        $emailOtpService->verify(
            user: $user,
            purpose: 'login',
            inputCode: (string) $request->string('code'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        Auth::login($user, $remember);

        $request->session()->forget([
            'auth_otp.pending_user_id',
            'auth_otp.pending_remember',
            'auth_otp.pending_purpose',
            'auth_otp.pending_email',
        ]);

        return redirect()->intended(config('fortify.home', '/dashboard'));
    }

    public function resendPending(Request $request, EmailOtpService $emailOtpService): RedirectResponse
    {
        $userId = $request->session()->get('auth_otp.pending_user_id');

        if (! $userId) {
            return to_route('login');
        }

        $user = User::findOrFail($userId);

        $emailOtpService->issue(
            user: $user,
            purpose: 'login',
            action: 'resend',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'otp-resent');
    }

    public function verifyEmailOtp(Request $request, EmailOtpService $emailOtpService): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        $emailOtpService->verify(
            user: $user,
            purpose: 'verify-email',
            inputCode: (string) $request->string('code'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return to_route('dashboard')->with('status', 'email-verified');
    }

    public function resendVerifyEmailOtp(Request $request, EmailOtpService $emailOtpService): RedirectResponse
    {
        $emailOtpService->issue(
            user: $request->user(),
            purpose: 'verify-email',
            action: 'resend',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'otp-resent');
    }
}
