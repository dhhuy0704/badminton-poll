<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
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
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($vote) {
            if (!isset($vote->poll_uuid)) {
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
    protected $table = 'votes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'player_name',
        'number_go_with',
        'poll_uuid',
        'voted_date',
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