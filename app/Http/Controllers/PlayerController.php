<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PlayerController extends Controller
{
    /**
     * Get a list of user names ordered alphabetically.
     *
     * @return JsonResponse
     */
    public function getPlayerList(): JsonResponse
    {
        $userNames = Player::orderBy('name', 'asc')->pluck('name');

        return response()->json($userNames);
    }
    
    /**
     * Display the player's profile
     *
     * @param  string  $uuid
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function profile($uuid, Request $request)
    {
        $player = Player::where('uuid', $uuid)->firstOrFail();
        
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subMonths(6);
            
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();
        
        // Get player statistics for the specified date range
        $statistics = $player->getStatistics($startDate, $endDate);
        
        // Add debug information
        $debug = [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'hasVotes' => count($statistics['votes']) > 0,
            'hasMonthlyStats' => count($statistics['monthlyStats']) > 0,
            'votesCount' => count($statistics['votes']),
            'monthlyStatsCount' => count($statistics['monthlyStats']),
        ];
        
        return view('player.profile', [
            'player' => $player,
            'statistics' => $statistics,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'debug' => $debug
        ]);
    }
}
