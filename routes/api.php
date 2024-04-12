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
    $url = "{$baseUrl}/sent-text";

    // client token is Client-Token header

    // $zapiResponse = Http::withHeader('Client-Token', $zApiClientToken)
    //     ->post($url, [
    //         'phone' => '5584988300053',
    //         'message' => 'Hello, world!'
    //     ]);

    // log response
    // log title with timestamp
    // Log::info('Response from Z-API: ' . now());
    // Log::info($zapiResponse->status());
    // Log::info($zapiResponse->body());

    return response()->json([
        'url' => $url,
        // 'response' => $zapiResponse->json(),
        // 'status' => $zapiResponse->status()
    ]);
});