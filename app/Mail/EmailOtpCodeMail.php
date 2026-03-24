<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailOtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        public string $purpose,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your verification code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-code',
            with: [
                'name' => $this->user->name,
                'code' => $this->code,
                'purpose' => $this->purpose,
            ],
        );
    }
}
