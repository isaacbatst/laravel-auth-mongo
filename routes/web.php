<?php

use App\Models\Location;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/mapa', function () {
    // get all locations
    $locations = Location::all();
    $locations = $locations->transform(function ($location) {
        return [
            'lat' => $location->lat,
            'lng' => $location->lng,
        ];
    });
    return Inertia::render('Map', [
        'locations' => $locations
    ]);
})->name('mapa');

// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return Inertia::render('Dashboard');
//     })->name('dashboard');
// });
