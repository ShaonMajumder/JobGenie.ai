<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionStartedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription)
    {
    }

    public function build(): self
    {
        $plan = $this->subscription->plan;

        return $this->subject('Your JobGenie.ai subscription is active')
            ->view('emails.billing.notification')
            ->with([
                'title' => 'Subscription Activated',
                'lines' => [
                    "You're now on the {$plan?->name} plan.",
                    'You can start generating AI career packs with your new allowance immediately.',
                ],
                'ctaUrl' => route('billing.index'),
                'ctaLabel' => 'Open Billing Portal',
            ]);
    }
}
