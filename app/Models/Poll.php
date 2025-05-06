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
        'expected_number_court',
        'expected_price',
        'actual_number_court',
        'actual_price',
        'number_member_registered',
        'closed_date',
    ];

    /**
     * Get the member votes associated with the poll.
     */
    public function memberVotes()
    {
        return $this->hasMany(MemberVote::class);
    }

    /**
     * Get the latest poll where closed_date is null and count total number_go_with in member_votes table.
     *
     * @return \App\Models\Poll|null
     */
    public static function getLatestOpenPoll()
    {
        return self::withSum('memberVotes as total_registered', 'number_go_with')
            ->whereNull('closed_date')
            ->latest('poll_date')
            ->first();
    }

    /**
     * Get the latest poll where closed_date is null and count total number_go_with in member_votes table.
     *
     * @return \App\Models\Poll|null
     */
    public static function getLatestPoll()
    {
        return self::withSum('memberVotes as total_registered', 'number_go_with')
            ->latest('poll_date')
            ->first();
    }
}