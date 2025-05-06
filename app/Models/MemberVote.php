<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberVote extends Model
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($memberVote) {
            if (!isset($memberVote->poll_uuid)) {
                throw new \Exception('poll_uuid is required.');
            }
        });
    }

    /**
     * Define the foreign key relationship.
     */
    protected $with = ['poll'];

    /**
     * The foreign key constraint for poll_uuid.
     */
    protected $foreignKey = 'poll_uuid';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'member_votes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'player_name',
        'number_go_with',
        'poll_uuid',
        'vote_date',
        'individual_price',
    ];

    /**
     * Get the poll that owns the member vote.
     */
    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }
}