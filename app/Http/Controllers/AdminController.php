<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\Player;
use App\Models\Vote;
use Illuminate\Support\Str;

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
        $today = now()->startOfDay();
        
        return view('admin.dashboard', [
            'latestPolls' => $latestPolls,
            'latestPollId' => $latestPollId,
            'totalPlayers' => $totalPlayers,
            'totalPolls' => $totalPolls,
            'totalVotes' => $totalVotes,
            'today' => $today
        ]);
    }
    
    /**
     * Show the players list page.
     *
     * @return \Illuminate\Http\Response
     */
    public function players()
    {
        $allPlayers = Player::orderBy('name', 'asc')->get();
        $totalPlayers = Player::count();
        $activeTotalPlayers = Player::where('is_active', true)->count();
        
        return view('admin.players', [
            'allPlayers' => $allPlayers,
            'totalPlayers' => $totalPlayers,
            'activeTotalPlayers' => $activeTotalPlayers
        ]);
    }
    
    /**
     * Create a new player.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createPlayer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:players,email'
        ]);
        
        $player = new Player;
        $player->uuid = (string) Str::uuid();
        $player->name = $request->name;
        $player->email = $request->email;
        $player->is_active = true;
        $player->save();
        
        return redirect()->back()->with('success', 'Player created successfully.');
    }
    
    /**
     * Update an existing player.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePlayer(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,uuid',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:players,email,' . $request->player_id . ',uuid'
        ]);
        
        $player = Player::find($request->player_id);
        $player->name = $request->name;
        $player->email = $request->email;
        $player->save();
        
        return redirect()->back()->with('success', 'Player updated successfully.');
    }
    
    /**
     * Deactivate a player.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deactivatePlayer(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,uuid'
        ]);
        
        $player = Player::find($request->player_id);
        $player->is_active = false;
        $player->save();
        
        return redirect()->back()->with('success', 'Player deactivated successfully.');
    }
    
    /**
     * Reactivate a player.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reactivatePlayer(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,uuid'
        ]);
        
        $player = Player::find($request->player_id);
        $player->is_active = true;
        $player->save();
        
        return redirect()->back()->with('success', 'Player reactivated successfully.');
    }
}
