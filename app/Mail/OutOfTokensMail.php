<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OutOfTokensMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function build(): self
    {
        return $this->subject('You used all JobGenie.ai tokens')
            ->view('emails.billing.notification')
            ->with([
                'title' => 'Out of Tokens',
                'lines' => [
                    "You've used all prepaid AI tokens for this billing period.",
                    'Upgrade your plan or wait for the next cycle to continue generating career packs.',
                ],
                'ctaUrl' => route('billing.index'),
                'ctaLabel' => 'Upgrade Plan',
            ]);
    }
}
