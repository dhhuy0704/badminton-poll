<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Player extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'players';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * Indicates if the IDs are UUIDs.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get all player names ordered alphabetically.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllAvailablePlayers()
    {
        $latestPoll = Poll::whereNull('closed_date')->latest()->first();

        if (!$latestPoll) {
            return collect(); // Return an empty collection if no active poll exists
        }

        $votedPlayerUuids = $latestPoll->Votes()->pluck('player_uuid');

        return self::where('is_active', true)
            ->whereNotIn('uuid', $votedPlayerUuids)
            ->orderBy('name', 'asc')
            ->pluck('name', 'uuid');
    }
    
    /**
     * Get all active players with their information.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllActivePlayers()
    {
        return self::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }
    
    /**
     * Get the votes for this player
     */
    public function votes()
    {
        return $this->hasMany(Vote::class, 'player_uuid', 'uuid');
    }
    
    /**
     * Get player statistics for a date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getStatistics(Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) {
            $startDate = now()->subMonth();
        }
        
        if (!$endDate) {
            $endDate = now();
        }
        
        // Get all votes for this player in the given date range
        $votes = $this->votes()
            ->join('polls', 'votes.poll_uuid', '=', 'polls.uuid')
            ->where('polls.poll_date', '>=', $startDate)
            ->where('polls.poll_date', '<=', $endDate)
            ->orderBy('polls.poll_date', 'asc')
            ->select('votes.*', 'polls.poll_date', 'polls.total_price')
            ->get();
        
        // Calculate total money spent
        $totalMoneySpent = $votes->sum(function($vote) {
            // Get total votes for this poll to calculate price per vote
            $totalVotesInPoll = Vote::where('poll_uuid', $vote->poll_uuid)->sum('slot');
            if ($totalVotesInPoll > 0) {
                $pricePerVote = $vote->total_price / $totalVotesInPoll;
                return $pricePerVote * $vote->slot;
            }
            return 0;
        });
        
        // Group votes by month for chart visualization
        $monthlyStats = $votes->groupBy(function($vote) {
            return Carbon::parse($vote->poll_date)->format('Y-m');
        })->map(function($monthVotes) {
            // Calculate money spent for this month
            $monthlySpent = $monthVotes->sum(function($vote) {
                $totalVotesInPoll = Vote::where('poll_uuid', $vote->poll_uuid)->sum('slot');
                if ($totalVotesInPoll > 0) {
                    $pricePerVote = $vote->total_price / $totalVotesInPoll;
                    return $pricePerVote * $vote->slot;
                }
                return 0;
            });
            
            return [
                'count' => $monthVotes->count(),
                'totalSlots' => $monthVotes->sum('slot'),
                'moneySpent' => $monthlySpent,
                'month' => Carbon::parse($monthVotes->first()->poll_date)->format('M Y'),
            ];
        })->values();
        
        return [
            'totalGames' => $votes->count(),
            'totalSlots' => $votes->sum('slot'),
            'totalMoneySpent' => $totalMoneySpent,
            'monthlyStats' => $monthlyStats,
            'votes' => $votes,
        ];
    }
}
