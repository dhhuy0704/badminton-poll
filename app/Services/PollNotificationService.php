<?php

namespace App\Services;

use App\Models\Player;
use App\Models\Poll;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PollNotificationService
{
    private $messengerService;

    public function __construct(FacebookMessengerService $messengerService)
    {
        $this->messengerService = $messengerService;
    }

    /**
     * Send poll notification to all active players with Facebook IDs
     *
     * @param Poll $poll
     * @return array
     */
    public function notifyPlayersAboutNewPoll(Poll $poll)
    {
        $players = Player::where('is_active', true)
            ->whereNotNull('facebook_id')
            ->get();

        $results = [
            'total_players' => $players->count(),
            'successful_notifications' => 0,
            'failed_notifications' => 0,
            'errors' => []
        ];

        foreach ($players as $player) {
            try {
                $result = $this->messengerService->sendPollNotification(
                    $player->facebook_id,
                    $poll
                );

                if ($result !== false) {
                    $results['successful_notifications']++;
                    Log::info('Poll notification sent successfully', [
                        'player' => $player->name,
                        'facebook_id' => $player->facebook_id,
                        'poll_date' => $poll->poll_date
                    ]);
                } else {
                    $results['failed_notifications']++;
                    $results['errors'][] = "Failed to send to {$player->name} ({$player->facebook_id})";
                }

            } catch (\Exception $e) {
                $results['failed_notifications']++;
                $results['errors'][] = "Error sending to {$player->name}: " . $e->getMessage();
                
                Log::error('Failed to send poll notification', [
                    'player' => $player->name,
                    'facebook_id' => $player->facebook_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Poll notification batch completed', $results);
        return $results;
    }

    /**
     * Send poll reminder to players who haven't voted
     *
     * @param Poll $poll
     * @param int $hoursBeforeDeadline
     * @return array
     */
    public function sendPollReminder(Poll $poll, $hoursBeforeDeadline = 24)
    {
        // Get players who haven't voted yet
        $playersWithoutVotes = Player::where('is_active', true)
            ->whereNotNull('facebook_id')
            ->whereNotExists(function ($query) use ($poll) {
                $query->select(DB::raw(1))
                    ->from('votes')
                    ->whereRaw('votes.player_uuid = players.uuid')
                    ->where('votes.poll_uuid', $poll->uuid);
            })
            ->get();

        $results = [
            'total_players' => $playersWithoutVotes->count(),
            'successful_reminders' => 0,
            'failed_reminders' => 0,
            'errors' => []
        ];

        $reminderMessage = "⏰ Reminder: The badminton poll for " . 
            $poll->poll_date->format('l, F j, Y') . 
            " closes in {$hoursBeforeDeadline} hours!\n\n" .
            "Don't forget to vote if you want to join! 🏸";

        foreach ($playersWithoutVotes as $player) {
            try {
                $quickReplies = [
                    [
                        'content_type' => 'text',
                        'title' => '✅ Slot 1',
                        'payload' => 'VOTE_SLOT_1_' . $poll->uuid
                    ],
                    [
                        'content_type' => 'text',
                        'title' => '✅ Slot 2',
                        'payload' => 'VOTE_SLOT_2_' . $poll->uuid
                    ],
                    [
                        'content_type' => 'text',
                        'title' => '❌ Not Available',
                        'payload' => 'NO_VOTE_' . $poll->uuid
                    ]
                ];

                $result = $this->messengerService->sendQuickReply(
                    $player->facebook_id,
                    $reminderMessage,
                    $quickReplies
                );

                if ($result !== false) {
                    $results['successful_reminders']++;
                } else {
                    $results['failed_reminders']++;
                    $results['errors'][] = "Failed to send reminder to {$player->name}";
                }

            } catch (\Exception $e) {
                $results['failed_reminders']++;
                $results['errors'][] = "Error sending reminder to {$player->name}: " . $e->getMessage();
            }
        }

        Log::info('Poll reminder batch completed', $results);
        return $results;
    }

    /**
     * Send poll closure notification
     *
     * @param Poll $poll
     * @return array
     */
    public function notifyPollClosure(Poll $poll)
    {
        $players = Player::where('is_active', true)
            ->whereNotNull('facebook_id')
            ->get();

        $totalVotes = $poll->votes()->count();
        $message = "🔒 The poll for " . $poll->poll_date->format('l, F j, Y') . " has been closed!\n\n";
        $message .= "📊 Total votes received: {$totalVotes}\n";
        $message .= "Thanks to everyone who participated! 🏸";

        $results = [
            'total_players' => $players->count(),
            'successful_notifications' => 0,
            'failed_notifications' => 0,
            'errors' => []
        ];

        foreach ($players as $player) {
            try {
                $result = $this->messengerService->sendTextMessage(
                    $player->facebook_id,
                    $message
                );

                if ($result !== false) {
                    $results['successful_notifications']++;
                } else {
                    $results['failed_notifications']++;
                    $results['errors'][] = "Failed to notify {$player->name}";
                }

            } catch (\Exception $e) {
                $results['failed_notifications']++;
                $results['errors'][] = "Error notifying {$player->name}: " . $e->getMessage();
            }
        }

        Log::info('Poll closure notification batch completed', $results);
        return $results;
    }

    /**
     * Send custom message to all players or specific player
     *
     * @param string $message
     * @param string|null $playerUuid
     * @return array
     */
    public function sendCustomMessage($message, $playerUuid = null)
    {
        if ($playerUuid) {
            $players = Player::where('uuid', $playerUuid)
                ->where('is_active', true)
                ->whereNotNull('facebook_id')
                ->get();
        } else {
            $players = Player::where('is_active', true)
                ->whereNotNull('facebook_id')
                ->get();
        }

        $results = [
            'total_players' => $players->count(),
            'successful_messages' => 0,
            'failed_messages' => 0,
            'errors' => []
        ];

        foreach ($players as $player) {
            try {
                $result = $this->messengerService->sendTextMessage(
                    $player->facebook_id,
                    $message
                );

                if ($result !== false) {
                    $results['successful_messages']++;
                } else {
                    $results['failed_messages']++;
                    $results['errors'][] = "Failed to send to {$player->name}";
                }

            } catch (\Exception $e) {
                $results['failed_messages']++;
                $results['errors'][] = "Error sending to {$player->name}: " . $e->getMessage();
            }
        }

        Log::info('Custom message batch completed', $results);
        return $results;
    }
}