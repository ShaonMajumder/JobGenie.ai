<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowTokensWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $remaining,
        public int $included
    ) {
    }

    public function build(): self
    {
        $percent = $this->included > 0
            ? round(($this->remaining / $this->included) * 100, 1)
            : 0;

        return $this->subject('You are running low on JobGenie.ai tokens')
            ->view('emails.billing.notification')
            ->with([
                'title' => 'Low AI Token Reminder',
                'lines' => [
                    "You have {$this->remaining} tokens remaining ({$percent}% of your monthly allowance).",
                    'Consider upgrading to avoid interruptions.',
                ],
                'ctaUrl' => route('billing.index'),
                'ctaLabel' => 'Review Plans',
            ]);
    }
}
