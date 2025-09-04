<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    TicketController, NoteController, ClassController, AnalyticsController
};

Route::apiResource('tickets', TicketController::class);
Route::post('tickets/{ticket}/classify', [ClassController::class, 'enqueue']);
Route::apiResource('tickets.notes', NoteController::class)->shallow()->only(['store','update','destroy']);
Route::get('analytics/summary', [AnalyticsController::class, 'summary']);
