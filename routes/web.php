<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\PollController::class, 'index']);
Route::get('/poll', [\App\Http\Controllers\PollController::class, 'index']);
Route::post('/poll', [\App\Http\Controllers\VoteController::class, 'vote']);
Route::get('/latest-list', [\App\Http\Controllers\PollController::class, 'latest_list']);
Route::post('cancel-vote', [\App\Http\Controllers\VoteController::class, 'cancel_vote']);

Route::get('/rule', function () {
    return view('rule');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard']);
    Route::post('/create-poll', [\App\Http\Controllers\PollController::class, 'create']);
    Route::post('/close-poll', [\App\Http\Controllers\PollController::class, 'closePoll']);
    Route::post('/reopen-poll', [\App\Http\Controllers\PollController::class, 'reopenPoll']);
    Route::post('/update-poll', [\App\Http\Controllers\PollController::class, 'updatePoll']);
});
