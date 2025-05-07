<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    protected $primaryKey = 'id';

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

        return self::whereNotIn('uuid', $votedPlayerUuids)
            ->orderBy('name', 'asc')
            ->pluck('name', 'uuid');
    }
}
