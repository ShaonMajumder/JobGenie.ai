<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }

    public function build(): self
    {
        return $this->subject('JobGenie.ai Invoice Ready')
            ->view('emails.billing.notification')
            ->with([
                'title' => 'Monthly Invoice Generated',
                'lines' => [
                    "Subscription: {$this->invoice->currency} ".number_format($this->invoice->amount_subscription, 2),
                    "AI Usage: {$this->invoice->currency} ".number_format($this->invoice->amount_ai_usage, 2),
                    "Total: {$this->invoice->currency} ".number_format($this->invoice->amount_total, 2),
                ],
                'ctaUrl' => route('billing.index'),
            ]);
    }
}
