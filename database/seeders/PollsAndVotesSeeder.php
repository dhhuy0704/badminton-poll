<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Poll;
use App\Models\Vote;
use App\Models\Player;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PollsAndVotesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a list of player UUIDs from the database
        $playerUuids = Player::where('is_active', true)->pluck('uuid')->toArray();
        
        if (empty($playerUuids)) {
            $this->command->error('No active players found in database. Please seed players first.');
            return;
        }
        
        $this->command->info('Creating polls and votes for the past 6 months...');
        
        // Calculate dates: June 20, 2025 back to December 20, 2024
        $currentDate = Carbon::parse('2025-06-20');
        $startDate = $currentDate->copy()->subMonths(6);
        
        // Weekly polls - every Saturday
        $pollDate = $startDate->copy()->startOfWeek()->next(Carbon::SATURDAY);
        
        // Create polls for each week
        while ($pollDate->lte($currentDate)) {
            // Create a poll for this Saturday
            $pollUuid = (string) Str::uuid();
            $totalCourts = rand(1, 3); // Random number of courts between 1 and 3
            $totalHours = rand(3, 5);  // Random number of hours between 3 and 5
            $courtRate = 30;           // $30 per court per hour
            $totalPrice = $totalCourts * $totalHours * $courtRate;
            
            $poll = new Poll();
            $poll->uuid = $pollUuid;
            $poll->poll_date = $pollDate->format('Y-m-d');
            $poll->total_court = $totalCourts;
            $poll->total_hours = $totalHours;
            $poll->total_price = $totalPrice;
            
            // Close polls that are in the past
            if ($pollDate->lt($currentDate)) {
                $closingDate = $pollDate->copy()->subDays(rand(1, 3)); // Close the poll 1-3 days before event
                $poll->closed_date = $closingDate;
            }
            
            $poll->save();
            
            // Create votes for each poll (between 8 and 16 votes)
            $numVotes = rand(8, 16);
            $selectedPlayers = array_rand(array_flip($playerUuids), min($numVotes, count($playerUuids)));
            
            // Ensure $selectedPlayers is always an array
            if (!is_array($selectedPlayers)) {
                $selectedPlayers = [$selectedPlayers];
            }
            
            $totalSlots = 0;
            $votes = [];
            
            foreach ($selectedPlayers as $playerUuid) {
                $slot = rand(1, 2); // 1 or 2 slots per player
                $totalSlots += $slot;
                
                $voteDate = $pollDate->copy()->subDays(rand(2, 6));
                
                $vote = new Vote();
                $vote->uuid = (string) Str::uuid();
                $vote->player_uuid = $playerUuid;
                $vote->poll_uuid = $pollUuid;
                $vote->slot = $slot;
                $vote->voted_date = $voteDate;
                $votes[] = $vote;
            }
            
            // Calculate individual price
            $pricePerSlot = $totalSlots > 0 ? $totalPrice / $totalSlots : 0;
            
            foreach ($votes as $vote) {
                $vote->individual_price = $vote->slot * $pricePerSlot;
                $vote->save();
            }
            
            $this->command->info("Created poll for {$pollDate->format('Y-m-d')} with {$numVotes} votes");
            
            // Move to the next Saturday
            $pollDate->addWeek();
        }
        
        $this->command->info('Seeding complete!');
    }
}
