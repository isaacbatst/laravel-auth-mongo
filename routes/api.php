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

Route::post('/whatsapp/send', [App\Http\Controllers\WhatsappController::class, 'sendTest']);
Route::post('/whatsapp/receive', [App\Http\Controllers\WhatsappController::class, 'receive']);