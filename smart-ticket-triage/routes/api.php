<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    TicketController, NoteController, ClassController, AnalyticsController
};

Route::apiResource('tickets', TicketController::class);
Route::post('tickets/{ticket}/classify', [ClassController::class, 'enqueue']);
Route::apiResource('tickets.notes', NoteController::class)->shallow()->only(['store','update','destroy']);
Route::get('stats', [AnalyticsController::class, 'summary']);