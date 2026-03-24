<?php

use App\Mail\EmailOtpCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('pending login can be completed with a valid otp', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('auth.otp.challenge', absolute: false));

    $code = null;

    Mail::assertSent(EmailOtpCodeMail::class, function (EmailOtpCodeMail $mail) use (&$code) {
        $code = $mail->code;

        return true;
    });

    $this->post(route('auth.otp.verify'), [
        'code' => $code,
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});
