<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->session()->has('auth_otp.pending_user_id')) {
            return $request->wantsJson()
                ? response()->json(['otp_required' => true])
                : to_route('auth.otp.challenge')->with('status', 'otp-sent');
        }

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended(Fortify::redirects('login'));
    }
}
