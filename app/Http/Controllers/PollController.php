<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\MemberVote;
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
        $allPlayer  = Player::getAllPlayers();

        return view('index', [
            'allPlayer'  => $allPlayer,
            'latestPoll' => $latestPoll
        ]);
    }

    /**
     * Get data from form submission and store it in the database.s
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function vote(Request $request)
    {

        $validatedData = $request->validate([
            'poll_uuid'   => 'required|uuid|exists:polls,uuid',
            'player_uuid' => 'required|uuid|exists:players,uuid',
            'go_with'     => 'required|integer|min:1|max:3',
        ]);

        $MemberVote                 = new MemberVote();
        $MemberVote->uuid           = (string) Str::uuid();
        $MemberVote->player_uuid    = $validatedData['player_uuid'];
        $MemberVote->number_go_with = $validatedData['go_with'];
        $MemberVote->poll_uuid      = $validatedData['poll_uuid'];
        $MemberVote->vote_date      = now();

        // Check if the player has already voted for the same poll
        $existingVote = MemberVote::where('poll_uuid', $validatedData['poll_uuid'])
            ->where('player_uuid', $validatedData['player_uuid'])
            ->first();

        if ($existingVote) {
            return redirect()->back()->withErrors(['player_uuid' => 'You have already voted for this poll.']);
        }

        $vote_status = $MemberVote->save();

        // Auto close poll if the number of votes reaches the maximum number of courts
        $totalGoWith = MemberVote::where('poll_uuid', $validatedData['poll_uuid'])->sum('number_go_with');
        if ($totalGoWith >= config('constants.MAX_NUMBER_COURT_REGISTER')) {
            Poll::where('uuid', $validatedData['poll_uuid'])->update(['closed_date' => now()]);
        }

        return $this->latest_list();
    }

    /**
     * Show the form for creating a new poll.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    public function create(Request $request)
    {
        $constants = config('constants');
        $defaultPricePerCourtPerHour = $constants['DEFAULT_PRICE_PER_COURT_PER_HOUR'];
        $defaultHoursPerDay          = $constants['DEFAULT_HOURS_PER_DAY'];
        $defaultDayOfWeek            = $constants['DEFAULT_DAY_OF_WEEK'];
        $defaultNumberCourt          = $constants['DEFAULT_NUMBER_COURT'];

        $defaultPrice                = $defaultNumberCourt * $defaultPricePerCourtPerHour * $defaultHoursPerDay;
        $saveMoneyMode               = $request->input('save_money_mode', 1);

        if ($saveMoneyMode === 1) {
            /**
             * At default, we play 1 court in 3 hours and 1 court in 2 hours to save money.
             * If members registered max
             */
            $maxSavedHoursAllCourt = 1 * $defaultPricePerCourtPerHour;
            $defaultPrice = ($defaultPrice - $maxSavedHoursAllCourt) * (1 + $constants['PROVINCE_TAX_RATE']);
        }

        $request->merge([
            'poll_date'             => $request->input('poll_date', now()->next($defaultDayOfWeek)->toDateString()),
            'expected_number_court' => $request->input('expected_number_court', $defaultNumberCourt),
            'expected_price'        => $request->input('expected_price', $defaultPrice),
        ]);

        $validatedData = $request->validate([
            'poll_date'                => 'required|date',
            'expected_number_court'    => 'required|integer|min:1',
            'expected_price'           => 'required|numeric|min:0',
        ]);

        $poll                        = new Poll();
        $poll->uuid                  = (string) Str::uuid();
        $poll->poll_date             = $validatedData['poll_date'];
        $poll->save_money_mode       = $saveMoneyMode;
        $poll->expected_number_court = $validatedData['expected_number_court'];
        $poll->expected_price        = $validatedData['expected_price'];
        $poll->save();

        return $poll->exists ? true : false;
    }

    public function latest_list()
    {
        $latestPoll = Poll::getLatestPoll();

        $votes = MemberVote::where('poll_uuid', $latestPoll->uuid)
            ->join('players', 'member_votes.player_uuid', '=', 'players.uuid')
            ->select('players.name as player_name', 'member_votes.number_go_with')
            ->get();

        return view('latest_list', [
            'latestPoll' => $latestPoll,
            'votes'      => $votes,
        ]);
    }

}
