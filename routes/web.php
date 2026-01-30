<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OpportunityController;
use Illuminate\Support\Facades\Route;

// Login routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Home and search routes (protected)
Route::middleware('web')->group(function () {
    Route::get('/home', [OpportunityController::class, 'home'])->name('home');
    Route::get('/search', [OpportunityController::class, 'search'])->name('opportunities.search');
    Route::post('/apply/{id}', [OpportunityController::class, 'apply'])->name('opportunities.apply');
});
