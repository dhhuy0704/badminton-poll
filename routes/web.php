<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\PollController::class, 'index']);
Route::get('/poll', [\App\Http\Controllers\PollController::class, 'index']);
Route::post('/poll', [\App\Http\Controllers\VoteController::class, 'vote']);
Route::get('/latest-list', [\App\Http\Controllers\PollController::class, 'latest_list']);
Route::post('cancel-vote', [\App\Http\Controllers\VoteController::class, 'cancel_vote']);
Route::get('/player/{uuid}', [\App\Http\Controllers\PlayerController::class, 'profile'])->name('player.profile');
Route::get('language/{locale}', [\App\Http\Controllers\LanguageController::class, 'switchLang'])->name('language.switch');

Route::get('/rule', function () {
    return view('rule');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard']);
    Route::get('/players', [\App\Http\Controllers\AdminController::class, 'players']);
    Route::post('/players', [\App\Http\Controllers\AdminController::class, 'createPlayer']);
    Route::put('/players/update', [\App\Http\Controllers\AdminController::class, 'updatePlayer']);
    Route::put('/players/deactivate', [\App\Http\Controllers\AdminController::class, 'deactivatePlayer']);
    Route::post('/create-poll', [\App\Http\Controllers\PollController::class, 'create']);
    Route::post('/close-poll', [\App\Http\Controllers\PollController::class, 'closePoll']);
    Route::post('/reopen-poll', [\App\Http\Controllers\PollController::class, 'reopenPoll']);
    Route::post('/update-poll', [\App\Http\Controllers\PollController::class, 'updatePoll']);
});
