<?php

use App\Mail\EmailOtpCodeMail;
use App\Models\EmailOtp;
use App\Models\OtpAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('otp resend enforces 60 second cooldown and logs failure', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('auth.otp.challenge', absolute: false));

    $this->post(route('auth.otp.resend'))
        ->assertSessionHasErrors('code');

    expect(OtpAuditLog::where('user_id', $user->id)
        ->where('purpose', 'login')
        ->where('action', 'resend')
        ->where('status', 'failed')
        ->where('reason', 'cooldown')
        ->exists())->toBeTrue();
});

test('otp verification locks after three invalid attempts and logs lockout', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('auth.otp.challenge', absolute: false));

    for ($i = 0; $i < 3; $i++) {
        $this->post(route('auth.otp.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');
    }

    $this->post(route('auth.otp.verify'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect(OtpAuditLog::where('user_id', $user->id)
        ->where('purpose', 'login')
        ->where('action', 'verify')
        ->where('status', 'failed')
        ->where('reason', 'attempts_exceeded')
        ->exists())->toBeTrue();
});

test('otp expires after ten minutes and logs expiry', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('auth.otp.challenge', absolute: false));

    $code = null;

    Mail::assertSent(EmailOtpCodeMail::class, function (EmailOtpCodeMail $mail) use (&$code) {
        $code = $mail->code;

        return true;
    });

    EmailOtp::query()
        ->where('user_id', $user->id)
        ->where('purpose', 'login')
        ->update(['expires_at' => now()->subMinute()]);

    $this->post(route('auth.otp.verify'), ['code' => $code])
        ->assertSessionHasErrors('code');

    expect(OtpAuditLog::where('user_id', $user->id)
        ->where('purpose', 'login')
        ->where('action', 'verify')
        ->where('status', 'failed')
        ->where('reason', 'expired')
        ->exists())->toBeTrue();
});

test('successful otp verification logs success', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('auth.otp.challenge', absolute: false));

    $code = null;

    Mail::assertSent(EmailOtpCodeMail::class, function (EmailOtpCodeMail $mail) use (&$code) {
        $code = $mail->code;

        return true;
    });

    $this->post(route('auth.otp.verify'), ['code' => $code])
        ->assertRedirect(route('dashboard', absolute: false));

    expect(OtpAuditLog::where('user_id', $user->id)
        ->where('purpose', 'login')
        ->where('action', 'verify')
        ->where('status', 'success')
        ->exists())->toBeTrue();
});

test('registration issues verify-email otp and writes audit log', function () {
    Mail::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Audit User',
        'email' => 'audit@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('verification.notice', absolute: false));
    $response->assertSessionHas('status', 'otp-sent');

    $user = User::where('email', 'audit@example.com')->firstOrFail();

    expect(EmailOtp::where('user_id', $user->id)
        ->where('purpose', 'verify-email')
        ->exists())->toBeTrue();

    expect(OtpAuditLog::where('user_id', $user->id)
        ->where('purpose', 'verify-email')
        ->where('action', 'issue')
        ->where('status', 'success')
        ->exists())->toBeTrue();
});
