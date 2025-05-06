<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('poll:create {poll_date?} {expected_number_court?} {expected_price?} {save_money_mode?}', function ($poll_date = null, $expected_number_court = null, $expected_price = null, $save_money_mode = null) {
    $controller = app(\App\Http\Controllers\PollController::class);

    if (is_null($poll_date) && is_null($expected_number_court) && is_null($expected_price) && is_null($save_money_mode)) {
        $request = new \Illuminate\Http\Request();
    } else {
        $request = new \Illuminate\Http\Request([
            'poll_date'             => $poll_date,
            'expected_number_court' => $expected_number_court,
            'expected_price'        => $expected_price,
            'save_money_mode'       => $save_money_mode,
        ]);
    }
    if (!$controller->create($request)) {
        $this->error('Failed to create poll.');
        return false;
    }

    $this->info('Poll created successfully.');

})->purpose('Create a new poll');
