<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KillBillWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::info('Kill Bill webhook received', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        return response()->json(['status' => 'ok']);
    }
}
