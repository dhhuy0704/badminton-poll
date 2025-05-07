<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('poll:create {poll_date?} {total_hour?} {unit_price?}',
function ($poll_date = null, $total_hour = null, $unit_price = null) {

    $controller = app(\App\Http\Controllers\PollController::class);

    if (is_null($poll_date) && is_null($total_hour) && is_null($unit_price)) {
        $request = new \Illuminate\Http\Request();
    } else {
        $request = new \Illuminate\Http\Request([
            'poll_date'  => $poll_date,
            'total_hour' => $total_hour,
            'unit_price' => $unit_price,
        ]);
    }

    if (!$controller->create($request)) {
        $this->error('Failed to create poll.');
        return false;
    }

    $this->info('Poll created successfully.');

})->purpose('Create a new poll');
