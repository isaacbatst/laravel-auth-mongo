<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function receive(Request $request)
    {
        $body = $request->all();

        Log::info('Received message from Z-API: ' . now());
        Log::info($body);

        if (!isset($body['text'])) {
            // return empty with 200
            return response()->json([]);
        }

        $data = [
            'phone' => $body['phone'],
            'message' => $body['text']['message']
        ];

        return response()->json($data);
    }
}
