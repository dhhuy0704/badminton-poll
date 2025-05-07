<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Vote;
use App\Models\Poll;
use App\Models\Player;

class PollController extends AppController
{
    /**
     * Main page of the poll
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $latestPoll = Poll::getLatestOpenPoll();
        $allPlayer  = Player::getAllAvailablePlayers();

        return view('index', [
            'allPlayer'  => $allPlayer,
            'latestPoll' => $latestPoll
        ]);
    }


    /**
     * Show the form for creating a new poll.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    public function create(Request $request)
    {

        $validatedData = $request->validate([
            'poll_date'  => 'nullable|date',
            'total_hour' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $defaultPollDate = now()->next(config('constants.DEFAULT_DAY_OF_WEEK'))->startOfDay();
        $validatedData['poll_date'] = $request->input('poll_date') ? Carbon::parse($request->input('poll_date'))->startOfDay() : $defaultPollDate;

        $validatedData['total_hours'] = $validatedData['total_hour'] ?? config('constants.DEFAULT_TOTAL_HOURS');
        $validatedData['total_court'] = config('constants.DEFAULT_TOTAL_COURT');
        $unitPrice = $validatedData['unit_price'] ?? config('constants.DEFAULT_PRICE_PER_COURT_PER_HOUR');
        $totalPriceBeforeTax = $validatedData['total_hours'] * $unitPrice;
        $validatedData['total_price'] = $totalPriceBeforeTax + ($totalPriceBeforeTax * config('constants.PROVINCE_TAX_RATE'));

        $poll              = new Poll();
        $poll->uuid        = (string) Str::uuid();
        $poll->poll_date   = $validatedData['poll_date'];
        $poll->total_court = $validatedData['total_court'];
        $poll->total_hours = $validatedData['total_hours'];
        $poll->total_price = $validatedData['total_price'];
        $poll->save();

        return $poll->exists ? true : false;
    }

    public function latest_list()
    {
        $latestPoll = Poll::getLatestPoll();

        $votes = Vote::where('poll_uuid', $latestPoll->uuid)
            ->join('players', 'votes.player_uuid', '=', 'players.uuid')
            ->select('players.name as player_name', 'votes.slot', 'votes.uuid')
            ->get();

        return view('latest_list', [
            'latestPoll' => $latestPoll,
            'votes'      => $votes,
        ]);
    }

}
