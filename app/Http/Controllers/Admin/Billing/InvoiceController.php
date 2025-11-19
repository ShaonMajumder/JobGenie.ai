<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['user', 'subscription.plan'])
            ->latest('period_start')
            ->paginate(20);

        return view('admin.billing.invoices.index', compact('invoices'));
    }
}
