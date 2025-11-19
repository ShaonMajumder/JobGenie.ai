<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }

    public function build(): self
    {
        return $this->subject('JobGenie.ai payment failed')
            ->view('emails.billing.notification')
            ->with([
                'title' => 'Payment Failed',
                'lines' => [
                    'We were unable to process your latest payment.',
                    'Please update your billing information to keep AI features active.',
                ],
                'ctaUrl' => route('billing.index'),
                'ctaLabel' => 'Review Billing',
            ]);
    }
}
