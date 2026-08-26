<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class InfinizedWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $secret = config('infinized.webhook_secret');

        if ($secret !== null && $secret !== '') {
            $signature = $request->header('X-Infinized-Signature', '');
            $body = $request->getContent();
            $expected = 'sha256='.hash_hmac('sha256', $body, $secret);

            if (! hash_equals($expected, $signature)) {
                abort(401, 'Invalid webhook signature.');
            }
        }

        Log::info('infinized.webhook', [
            'event' => $request->input('event'),
            'payload' => $request->all(),
        ]);

        return response('OK', 200);
    }
}
