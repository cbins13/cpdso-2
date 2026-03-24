<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request, EmailOtpService $emailOtpService): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        // 1. Find by google_id (returning user who already linked)
        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            // 2. Find by email (existing email/password account — auto-link)
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update(['google_id' => $googleUser->getId()]);
            } else {
                // 3. New user — auto-create from Google profile
                $user = User::create([
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password'  => null,
                ]);
            }
        }

        $emailOtpService->issue(
            user: $user,
            purpose: 'login',
            action: 'issue',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            meta: ['provider' => 'google'],
        );

        $request->session()->put([
            'auth_otp.pending_user_id' => $user->id,
            'auth_otp.pending_remember' => true,
            'auth_otp.pending_purpose' => 'login',
            'auth_otp.pending_email' => $user->email,
        ]);

        return to_route('auth.otp.challenge')->with('status', 'otp-sent');
    }
}
