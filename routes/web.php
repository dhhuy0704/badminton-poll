<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\PollController::class, 'index']);
Route::get('/poll', [\App\Http\Controllers\PollController::class, 'index']);
Route::post('/poll', [\App\Http\Controllers\PollController::class, 'vote']);
Route::get('/latest-list', [\App\Http\Controllers\PollController::class, 'latest_list']);

Route::get('/rule', function () {
    return view('rule');
});
