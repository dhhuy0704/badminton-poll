<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Vote;
use App\Models\Poll;
use App\Models\Player;

class VoteController extends AppController
{

    /**
     * Get data from form submission and store it in the database.s
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function vote(Request $request)
    {

        $validatedData = $request->validate([
            'poll_uuid'   => 'required|uuid|exists:polls,uuid',
            'player_uuid' => 'required|uuid|exists:players,uuid',
            'slot'        => 'required|integer|min:1|max:2',
        ]);

        $Vote              = new Vote();
        $Vote->uuid        = (string) Str::uuid();
        $Vote->player_uuid = $validatedData['player_uuid'];
        $Vote->slot        = $validatedData['slot'];
        $Vote->poll_uuid   = $validatedData['poll_uuid'];
        $Vote->voted_date  = now();

        // Check if the player has already voted for the same poll
        $existingVote = Vote::where('poll_uuid', $validatedData['poll_uuid'])
            ->where('player_uuid', $validatedData['player_uuid'])
            ->first();

        if ($existingVote) {
            return redirect()->back()->withErrors(['player_uuid' => 'You have already voted for this poll.']);
        }

        $vote_status = $Vote->save();
        
        if ($vote_status) {
            $totalSlot = Vote::where('poll_uuid', $validatedData['poll_uuid'])->sum('slot');
            // Auto close poll if the number of votes reaches the maximum number of courts
            if ($totalSlot >= config('constants.MAX_MEMBER_REGISTER')) {
                Poll::where('uuid', $validatedData['poll_uuid'])->update(['closed_date' => now()]);
            }
        }

        return redirect('/latest-list');
    }

    /**
     * Cancel a vote and remove it from the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cancel_vote(Request $request)
    {
        $validatedData = $request->validate([
            'vote_uuid' => 'required|uuid|exists:votes,uuid',
        ]);

        $vote = Vote::find($validatedData['vote_uuid']);

        if (!$vote) {
            return redirect('/latest-list')->withErrors(['vote_uuid' => 'Vote not found.']);
        }

        $poll = Poll::find($vote->poll_uuid);

        if (!$poll || !is_null($poll->closed_date)) {
            return redirect('/latest-list')->withErrors(['poll' => 'Poll is closed. Cannot cancel vote.']);
        }

        $vote->delete();

        return redirect('/latest-list')->with('success', 'Vote successfully canceled.');
    }
}
