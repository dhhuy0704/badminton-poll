<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('poll:create {poll_date?} {total_hour?} {total_court?} {unit_price?}',
function ($poll_date = null, $total_hour = null, $total_court = null, $unit_price = null) {

    $controller = app(\App\Http\Controllers\PollController::class);

    if (is_null($poll_date) && is_null($total_hour) && is_null($unit_price)) {
        $request = new \Illuminate\Http\Request();
    } else {
        $request = new \Illuminate\Http\Request([
            'poll_date'  => $poll_date,
            'total_hour' => $total_hour,
            'total_court' => $total_court,
            'unit_price' => $unit_price,
        ]);
    }

    $result = $controller->create($request);
    
    if (is_array($result)) {
        if ($result['success']) {
            $this->info($result['message']);
            $poll = $result['poll'];
            $this->table(
                ['UUID', 'Date', 'Courts', 'Hours', 'Total Price'],
                [[$poll->uuid, $poll->poll_date->format('Y-m-d'), $poll->total_court, $poll->total_hours, '$'.number_format($poll->total_price, 2)]]
            );
            return true;
        } else {
            $this->error($result['message']);
            return false;
        }
    }
    
    $this->error('Unexpected response from controller.');
    return false;

})->purpose('Create a new poll');

Artisan::command('poll:close-latest', function () {

    $controller = app(\App\Http\Controllers\PollController::class);
    $request = new \Illuminate\Http\Request();
    
    $result = $controller->closePoll($request);
    
    if (is_array($result)) {
        if ($result['success']) {
            $this->info($result['message']);
            $poll = $result['poll'];
            $this->table(
                ['UUID', 'Date', 'Courts', 'Hours', 'Total Price', 'Closed Date'],
                [[$poll->uuid, $poll->poll_date->format('Y-m-d'), $poll->total_court, $poll->total_hours, 
                  '$'.number_format($poll->total_price, 2), $poll->closed_date->format('Y-m-d H:i')]]
            );
            return true;
        } else {
            $this->error($result['message']);
            return false;
        }
    }
    
    $this->error('Unexpected response from controller.');
    return false;
})->purpose('Close the latest poll');

Artisan::command('poll:reopen {poll_uuid}', function ($poll_uuid) {
    $controller = app(\App\Http\Controllers\PollController::class);
    $request = new \Illuminate\Http\Request([
        'poll_uuid' => $poll_uuid,
    ]);
    
    $result = $controller->reopenPoll($request);
    
    if (is_array($result)) {
        if ($result['success']) {
            $this->info($result['message']);
            $poll = $result['poll'];
            $this->table(
                ['UUID', 'Date', 'Courts', 'Hours', 'Total Price'],
                [[$poll->uuid, $poll->poll_date->format('Y-m-d'), $poll->total_court, $poll->total_hours, 
                  '$'.number_format($poll->total_price, 2)]]
            );
            return true;
        } else {
            $this->error($result['message']);
            return false;
        }
    }
    
    $this->error('Unexpected response from controller.');
    return false;
})->purpose('Reopen a closed poll (poll date must be in the future)');
