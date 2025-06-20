<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'poll_date',
        'total_court',
        'total_price',
        'total_hours',
        'closed_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'poll_date'   => 'datetime',
        'closed_date' => 'datetime',
    ];

    /**
     * Get the votes associated with the poll.
     */
    public function Votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the latest poll where closed_date is null and count total slot in votes table.
     *
     * @return \App\Models\Poll|null
     */
    public static function getLatestOpenPoll()
    {
        return self::withSum('Votes as total_registered', 'slot')
            ->whereNull('closed_date')
            ->latest('poll_date')
            ->first();
    }

    /**
     * Get the latest poll where closed_date is null and count total slot in votes table.
     *
     * @return \App\Models\Poll|null
     */
    public static function getLatestPoll()
    {
        return self::withSum('Votes as total_registered', 'slot')
            ->latest('poll_date')
            ->first();
    }
}