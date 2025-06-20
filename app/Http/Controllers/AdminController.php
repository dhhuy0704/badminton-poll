<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\Player;
use App\Models\Vote;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $latestPolls = Poll::orderBy('poll_date', 'desc')->take(5)->get();
        $latestPollId = $latestPolls->first()->uuid ?? null;
        $totalPlayers = Player::count();
        $totalPolls = Poll::count();
        $totalVotes = Vote::count();
        
        return view('admin.dashboard', [
            'latestPolls' => $latestPolls,
            'latestPollId' => $latestPollId,
            'totalPlayers' => $totalPlayers,
            'totalPolls' => $totalPolls,
            'totalVotes' => $totalVotes
        ]);
    }
}
