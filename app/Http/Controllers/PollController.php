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
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'poll_date'  => 'nullable|date',
            'total_hour' => 'nullable|integer|min:1',
            'total_court' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $defaultPollDate = now()->next(config('constants.DEFAULT_DAY_OF_WEEK'))->startOfDay();
        $validatedData['poll_date'] = $request->input('poll_date') ? Carbon::parse($request->input('poll_date'))->startOfDay() : $defaultPollDate;
        $validatedData['total_hours'] = $validatedData['total_hour'] ?? config('constants.DEFAULT_TOTAL_HOURS');
        $validatedData['total_court'] = $validatedData['total_court'] ?? config('constants.DEFAULT_TOTAL_COURT');
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

        if ($poll->exists) {
            return redirect()->back()->with('success', 'Poll created successfully.');
        } else {
            return redirect()->back()->withErrors(['poll' => 'Failed to create poll.']);
        }
    }

    /**
     * Get list of players voted for the latest poll
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Close the specified poll or the current open poll
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function closePoll(Request $request)
    {
        if ($request->has('poll_uuid')) {
            $poll = Poll::where('uuid', $request->poll_uuid)->first();
            if (!$poll) {
                return redirect()->back()->withErrors(['poll' => 'Poll not found.']);
            }
        } else {
            $poll = Poll::getLatestOpenPoll();
        }
        
        $poll->closed_date = now();
        
        if ($poll->save()) {
            return redirect()->back()->with('success', 'Poll closed successfully.');
        }
        return redirect()->back()->withErrors(['poll' => 'Failed to close poll.']);
    }

    /**
     * Reopen a closed poll
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function reopenPoll(Request $request)
    {
        if ($request->has('poll_uuid')) {
            $poll = Poll::where('uuid', $request->poll_uuid)->first();
            if (!$poll) {
                return redirect()->back()->withErrors(['poll' => 'Poll not found.']);
            }
        } else {
            return redirect()->back()->withErrors(['poll' => 'Poll ID is required.']);
        }
        
        $poll->closed_date = null;
        
        if ($poll->save()) {
            return redirect()->back()->with('success', 'Poll reopened successfully.');
        }
        return redirect()->back()->withErrors(['poll' => 'Failed to reopen poll.']);
    }
    
    /**
     * Update an existing poll
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updatePoll(Request $request)
    {
        $validatedData = $request->validate([
            'poll_uuid'   => 'required|string',
            'poll_date'   => 'required|date',
            'total_hours' => 'required|integer|min:1',
            'total_court' => 'required|integer|min:1',
            'unit_price'  => 'required|numeric|min:0',
        ]);

        $poll = Poll::where('uuid', $validatedData['poll_uuid'])->first();
        
        if (!$poll) {
            return redirect()->back()->withErrors(['poll' => 'Poll not found.']);
        }

        // Check if poll is closed
        if ($poll->closed_date) {
            return redirect()->back()->withErrors(['poll' => 'Cannot edit a closed poll.']);
        }
        
        $poll->poll_date = Carbon::parse($validatedData['poll_date'])->startOfDay();
        $poll->total_hours = $validatedData['total_hours'];
        $poll->total_court = $validatedData['total_court'];
        
        $totalPriceBeforeTax = $validatedData['total_hours'] * $validatedData['unit_price'];
        $poll->total_price = $totalPriceBeforeTax + ($totalPriceBeforeTax * config('constants.PROVINCE_TAX_RATE'));
        
        if ($poll->save()) {
            return redirect()->back()->with('success', 'Poll updated successfully.');
        } else {
            return redirect()->back()->withErrors(['poll' => 'Failed to update poll.']);
        }
    }
}
