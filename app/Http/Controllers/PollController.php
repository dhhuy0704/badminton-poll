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
        
        // Check for existing poll on the same date
        $existingPoll = Poll::whereRaw('DATE(poll_date) = ?', [$validatedData['poll_date']->format('Y-m-d')])->first();
        if ($existingPoll) {
            $errorMessage = 'A poll already exists for this date.';
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => false, 'message' => $errorMessage];
            }
            return redirect()->back()->withErrors(['poll_date' => $errorMessage]);
        }
        
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
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => true, 'message' => 'Poll created successfully.', 'poll' => $poll];
            }
            return redirect()->back()->with('success', 'Poll created successfully.');
        } else {
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => false, 'message' => 'Failed to create poll.'];
            }
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
                if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                    return ['success' => false, 'message' => 'Poll not found.'];
                }
                return redirect()->back()->withErrors(['poll' => 'Poll not found.']);
            }
        } else {
            $poll = Poll::getLatestOpenPoll();
            if (!$poll) {
                if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                    return ['success' => false, 'message' => 'No open poll found.'];
                }
                return redirect()->back()->withErrors(['poll' => 'No open poll found.']);
            }
        }
        
        $poll->closed_date = now();
        
        if ($poll->save()) {
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => true, 'message' => 'Poll closed successfully.', 'poll' => $poll];
            }
            return redirect()->back()->with('success', 'Poll closed successfully.');
        }
        
        if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
            return ['success' => false, 'message' => 'Failed to close poll.'];
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
                if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                    return ['success' => false, 'message' => 'Poll not found.'];
                }
                return redirect()->back()->withErrors(['poll' => 'Poll not found.']);
            }
        } else {
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => false, 'message' => 'Poll ID is required.'];
            }
            return redirect()->back()->withErrors(['poll' => 'Poll ID is required.']);
        }
        
        // Check if the poll date is in the future
        if ($poll->poll_date->startOfDay()->lt(now()->startOfDay())) {
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => false, 'message' => 'Cannot reopen poll with a past date.'];
            }
            return redirect()->back()->withErrors(['poll' => 'Cannot reopen poll with a past date.']);
        }
        
        $poll->closed_date = null;
        
        if ($poll->save()) {
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => true, 'message' => 'Poll reopened successfully.', 'poll' => $poll];
            }
            return redirect()->back()->with('success', 'Poll reopened successfully.');
        }
        
        if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
            return ['success' => false, 'message' => 'Failed to reopen poll.'];
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
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => false, 'message' => 'Poll not found.'];
            }
            return redirect()->back()->withErrors(['poll' => 'Poll not found.']);
        }

        // Check if poll is closed
        if ($poll->closed_date) {
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => false, 'message' => 'Cannot edit a closed poll.'];
            }
            return redirect()->back()->withErrors(['poll' => 'Cannot edit a closed poll.']);
        }

        // Parse the input date string into a Carbon instance, preserving the date part only
        $newPollDateStr = Carbon::parse($validatedData['poll_date'])->format('Y-m-d');
        $currentPollDateStr = $poll->poll_date->format('Y-m-d');

        // Only check for date conflicts if the date is being changed
        if ($newPollDateStr !== $currentPollDateStr) {
            // Check for existing poll on the same date (excluding the current poll)
            $existingPoll = Poll::where('uuid', '!=', $validatedData['poll_uuid'])
                ->whereRaw('DATE(poll_date) = ?', [$newPollDateStr])
                ->first();

            if ($existingPoll) {
                if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                    return ['success' => false, 'message' => 'Another poll already exists for this date.'];
                }
                return redirect()->back()->withErrors(['poll_date' => 'Another poll already exists for this date.']);
            }

            // Update the poll date since it changed
            $poll->poll_date = Carbon::parse($validatedData['poll_date'])->startOfDay();
        }
        // If date hasn't changed, don't touch the poll_date field at all

        // Always update these fields
        $poll->total_hours = $validatedData['total_hours'];
        $poll->total_court = $validatedData['total_court'];
        
        $totalPriceBeforeTax = $validatedData['total_hours'] * $validatedData['unit_price'];
        $poll->total_price = $totalPriceBeforeTax + ($totalPriceBeforeTax * config('constants.PROVINCE_TAX_RATE'));
        
        if ($poll->save()) {
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => true, 'message' => 'Poll updated successfully.', 'poll' => $poll];
            }
            return redirect()->back()->with('success', 'Poll updated successfully.');
        } else {
            if ($request->wantsJson() || $request->is('api/*') || app()->runningInConsole()) {
                return ['success' => false, 'message' => 'Failed to update poll.'];
            }
            return redirect()->back()->withErrors(['poll' => 'Failed to update poll.']);
        }
    }
}
