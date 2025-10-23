<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Poll;
use App\Models\Vote;
use App\Services\FacebookMessengerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacebookMessengerController extends Controller
{
    private $messengerService;

    public function __construct(FacebookMessengerService $messengerService)
    {
        $this->messengerService = $messengerService;
    }

    /**
     * Handle webhook verification from Facebook
     *
     * @param Request $request
     * @return Response|string
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe') {
            $verificationResult = $this->messengerService->verifyWebhook($token, $challenge);
            if ($verificationResult !== false) {
                Log::info('Facebook webhook verified successfully');
                return response($verificationResult, 200);
            }
        }

        Log::warning('Facebook webhook verification failed', [
            'mode' => $mode,
            'token' => $token
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook events from Facebook
     *
     * @param Request $request
     * @return Response
     */
    public function webhook(Request $request)
    {
        $data = $request->all();
        
        Log::info('Facebook webhook received', ['data' => $data]);

        // Parse the webhook data
        $messages = $this->messengerService->parseWebhookData($data);

        foreach ($messages as $messageData) {
            $this->processMessage($messageData);
        }

        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Process individual message from webhook
     *
     * @param array $messageData
     * @return void
     */
    private function processMessage($messageData)
    {
        $senderId = $messageData['sender_id'];
        
        if (!$senderId) {
            return;
        }

        // Handle quick reply responses (votes)
        if (isset($messageData['quick_reply'])) {
            $this->handleQuickReply($senderId, $messageData['quick_reply']);
            return;
        }

        // Handle text messages
        if (isset($messageData['message']['text'])) {
            $this->handleTextMessage($senderId, $messageData['message']['text']);
            return;
        }

        // Handle postbacks
        if (isset($messageData['postback'])) {
            $this->handlePostback($senderId, $messageData['postback']);
            return;
        }
    }

    /**
     * Handle quick reply responses (voting)
     *
     * @param string $senderId
     * @param array $quickReply
     * @return void
     */
    private function handleQuickReply($senderId, $quickReply)
    {
        $payload = $quickReply['payload'] ?? '';
        
        Log::info('Processing quick reply', [
            'sender_id' => $senderId,
            'payload' => $payload
        ]);

        // Parse voting payload: VOTE_SLOT_1_uuid or NO_VOTE_uuid
        if (preg_match('/^VOTE_SLOT_(\d+)_(.+)$/', $payload, $matches)) {
            $slot = (int) $matches[1];
            $pollUuid = $matches[2];
            $this->processVote($senderId, $pollUuid, $slot);
        } elseif (preg_match('/^NO_VOTE_(.+)$/', $payload, $matches)) {
            $pollUuid = $matches[1];
            $this->processNoVote($senderId, $pollUuid);
        }
    }

    /**
     * Handle text messages
     *
     * @param string $senderId
     * @param string $text
     * @return void
     */
    private function handleTextMessage($senderId, $text)
    {
        $text = strtolower(trim($text));
        
        Log::info('Processing text message', [
            'sender_id' => $senderId,
            'text' => $text
        ]);

        // Find player by Facebook ID
        $player = Player::where('facebook_id', $senderId)->first();

        switch ($text) {
            case 'help':
            case 'start':
                $this->sendHelpMessage($senderId);
                break;
                
            case 'latest':
            case 'poll':
                $this->sendLatestPoll($senderId);
                break;
                
            case 'register':
                if (!$player) {
                    $this->startRegistration($senderId);
                } else {
                    $this->messengerService->sendTextMessage(
                        $senderId,
                        "You are already registered as {$player->name}! 🎉"
                    );
                }
                break;
                
            default:
                // Check if user is in registration process
                if (!$player) {
                    $this->completeRegistration($senderId, $text);
                } else {
                    $this->sendHelpMessage($senderId);
                }
                break;
        }
    }

    /**
     * Process vote submission
     *
     * @param string $senderId
     * @param string $pollUuid
     * @param int $slot
     * @return void
     */
    private function processVote($senderId, $pollUuid, $slot)
    {
        $player = Player::where('facebook_id', $senderId)->first();
        
        if (!$player) {
            $this->messengerService->sendTextMessage(
                $senderId,
                "Please register first by typing 'register' 📝"
            );
            return;
        }

        $poll = Poll::where('uuid', $pollUuid)->first();
        
        if (!$poll) {
            $this->messengerService->sendTextMessage(
                $senderId,
                "Sorry, this poll is no longer available ❌"
            );
            return;
        }

        // Check if user already voted
        $existingVote = Vote::where('poll_uuid', $pollUuid)
            ->where('player_uuid', $player->uuid)
            ->first();

        if ($existingVote) {
            $this->messengerService->sendTextMessage(
                $senderId,
                "You have already voted for this poll! Your current vote is for Slot {$existingVote->slot} ✅"
            );
            return;
        }

        // Create new vote
        $vote = new Vote();
        $vote->uuid = (string) Str::uuid();
        $vote->player_uuid = $player->uuid;
        $vote->slot = $slot;
        $vote->poll_uuid = $pollUuid;
        
        if ($vote->save()) {
            $this->messengerService->sendTextMessage(
                $senderId,
                "✅ Great! You've voted for Slot {$slot} on " . $poll->poll_date->format('F j, Y') . "\n\nThanks for participating! 🏸"
            );
            
            Log::info('Vote submitted via Messenger', [
                'player' => $player->name,
                'poll_date' => $poll->poll_date,
                'slot' => $slot
            ]);
        } else {
            $this->messengerService->sendTextMessage(
                $senderId,
                "Sorry, there was an error processing your vote. Please try again ❌"
            );
        }
    }

    /**
     * Process "no vote" response
     *
     * @param string $senderId
     * @param string $pollUuid
     * @return void
     */
    private function processNoVote($senderId, $pollUuid)
    {
        $player = Player::where('facebook_id', $senderId)->first();
        
        if (!$player) {
            $this->messengerService->sendTextMessage(
                $senderId,
                "Please register first by typing 'register' 📝"
            );
            return;
        }

        $this->messengerService->sendTextMessage(
            $senderId,
            "Thanks for letting us know you're not available for this session. Maybe next time! 😊"
        );
    }

    /**
     * Send help message
     *
     * @param string $senderId
     * @return void
     */
    private function sendHelpMessage($senderId)
    {
        $message = "🏸 Welcome to Badminton Poll Bot!\n\n";
        $message .= "Available commands:\n";
        $message .= "• 'latest' - Get the latest poll\n";
        $message .= "• 'register' - Register as a player\n";
        $message .= "• 'help' - Show this message\n\n";
        $message .= "I'll also notify you when new polls are available!";

        $this->messengerService->sendTextMessage($senderId, $message);
    }

    /**
     * Send latest poll information
     *
     * @param string $senderId
     * @return void
     */
    private function sendLatestPoll($senderId)
    {
        $latestPoll = Poll::getLatestOpenPoll();
        
        if (!$latestPoll) {
            $this->messengerService->sendTextMessage(
                $senderId,
                "No active polls at the moment. Check back later! 🏸"
            );
            return;
        }

        $this->messengerService->sendPollNotification($senderId, $latestPoll);
    }

    /**
     * Start player registration process
     *
     * @param string $senderId
     * @return void
     */
    private function startRegistration($senderId)
    {
        $this->messengerService->sendTextMessage(
            $senderId,
            "Welcome! 🎉 Please tell me your name to register for badminton polls."
        );
    }

    /**
     * Complete player registration
     *
     * @param string $senderId
     * @param string $name
     * @return void
     */
    private function completeRegistration($senderId, $name)
    {
        // Get user profile from Facebook
        $profile = $this->messengerService->getUserProfile($senderId);
        
        // Clean and validate name
        $name = trim($name);
        if (strlen($name) < 2) {
            $this->messengerService->sendTextMessage(
                $senderId,
                "Please provide a valid name (at least 2 characters)."
            );
            return;
        }

        // Check if name already exists
        $existingPlayer = Player::where('name', $name)->first();
        if ($existingPlayer) {
            $this->messengerService->sendTextMessage(
                $senderId,
                "A player with this name already exists. Please choose a different name or contact admin."
            );
            return;
        }

        // Create new player
        $player = new Player();
        $player->uuid = (string) Str::uuid();
        $player->name = $name;
        $player->facebook_id = $senderId;
        $player->is_active = true;

        if ($profile) {
            $player->facebook_profile = json_encode($profile);
        }

        if ($player->save()) {
            $this->messengerService->sendTextMessage(
                $senderId,
                "🎉 Registration successful! Welcome {$name}!\n\nYou'll now receive notifications about new badminton polls. Type 'latest' to see the current poll."
            );
            
            Log::info('New player registered via Messenger', [
                'name' => $name,
                'facebook_id' => $senderId
            ]);
        } else {
            $this->messengerService->sendTextMessage(
                $senderId,
                "Sorry, there was an error during registration. Please try again."
            );
        }
    }

    /**
     * Handle postback events
     *
     * @param string $senderId
     * @param array $postback
     * @return void
     */
    private function handlePostback($senderId, $postback)
    {
        $payload = $postback['payload'] ?? '';
        
        Log::info('Processing postback', [
            'sender_id' => $senderId,
            'payload' => $payload
        ]);

        switch ($payload) {
            case 'GET_STARTED':
                $this->sendHelpMessage($senderId);
                break;
                
            default:
                $this->sendHelpMessage($senderId);
                break;
        }
    }
}