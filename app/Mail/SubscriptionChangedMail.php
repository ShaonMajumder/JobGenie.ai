<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription)
    {
    }

    public function build(): self
    {
        $plan = $this->subscription->plan;

        return $this->subject('Your JobGenie.ai subscription was updated')
            ->view('emails.billing.notification')
            ->with([
                'title' => 'Subscription Updated',
                'lines' => [
                    "You're now on the {$plan?->name} plan.",
                    'Changes take effect immediately for the current billing period.',
                ],
                'ctaUrl' => route('billing.index'),
            ]);
    }
}
