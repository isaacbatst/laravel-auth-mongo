<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/whatsapp/send', function () {
    // $url = "https://api.z-api.io/instances/${}/sendText";
    // use template literals
    $baseUrl = config('app.zapi.url');
    $url = "{$baseUrl}/send-text";
    $clientToken = config('app.zapi.client_token');
    $phone = config('app.zapi.phone_to_send');

    // client token is Client-Token header

    $zapiResponse = Http::withHeader('Client-Token', $clientToken)
        ->post($url, [
            'phone' => $phone,
            'message' => 'Hello, world!'
        ]);

    // log response
    // log title with timestamp
    // Log::info('Response from Z-API: ' . now());
    // Log::info($zapiResponse->status());
    // Log::info($zapiResponse->body());

    return response()->json([
        'url' => $url,
        'response' => $zapiResponse->json(),
        'status' => $zapiResponse->status()
    ]);
});

Route::post('/whatsapp/receive', [App\Http\Controllers\WhatsappController::class, 'receive']);