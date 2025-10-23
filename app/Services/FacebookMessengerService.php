<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class FacebookMessengerService
{
    private $client;
    private $accessToken;
    private $verifyToken;
    private $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->accessToken = config('services.facebook.page_access_token');
        $this->verifyToken = config('services.facebook.verify_token');
        $this->apiUrl = 'https://graph.facebook.com/v18.0/me/messages';
    }

    /**
     * Verify webhook token from Facebook
     *
     * @param string $token
     * @param string $challenge
     * @return string|false
     */
    public function verifyWebhook($token, $challenge)
    {
        if ($token === $this->verifyToken) {
            return $challenge;
        }
        return false;
    }

    /**
     * Send a text message to a user
     *
     * @param string $recipientId
     * @param string $message
     * @return array|false
     */
    public function sendTextMessage($recipientId, $message)
    {
        $payload = [
            'recipient' => [
                'id' => $recipientId
            ],
            'message' => [
                'text' => $message
            ]
        ];

        return $this->sendMessage($payload);
    }

    /**
     * Send a message with quick replies (buttons)
     *
     * @param string $recipientId
     * @param string $text
     * @param array $quickReplies
     * @return array|false
     */
    public function sendQuickReply($recipientId, $text, $quickReplies)
    {
        $payload = [
            'recipient' => [
                'id' => $recipientId
            ],
            'message' => [
                'text' => $text,
                'quick_replies' => $quickReplies
            ]
        ];

        return $this->sendMessage($payload);
    }

    /**
     * Send poll notification with voting options
     *
     * @param string $recipientId
     * @param object $poll
     * @return array|false
     */
    public function sendPollNotification($recipientId, $poll)
    {
        $message = "🏸 New Badminton Poll Available!\n\n";
        $message .= "📅 Date: " . $poll->poll_date->format('l, F j, Y') . "\n";
        $message .= "🏟️ Courts: " . $poll->total_court . "\n";
        $message .= "⏰ Hours: " . $poll->total_hours . "\n";
        $message .= "💰 Total Price: $" . number_format($poll->total_price, 2) . "\n\n";
        $message .= "Would you like to join?";

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

        return $this->sendQuickReply($recipientId, $message, $quickReplies);
    }

    /**
     * Send message to Facebook Messenger API
     *
     * @param array $payload
     * @return array|false
     */
    private function sendMessage($payload)
    {
        try {
            $response = $this->client->post($this->apiUrl, [
                'json' => $payload,
                'query' => [
                    'access_token' => $this->accessToken
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            Log::info('Facebook message sent successfully', [
                'recipient_id' => $payload['recipient']['id'],
                'response' => $body
            ]);

            return $body;

        } catch (RequestException $e) {
            Log::error('Failed to send Facebook message', [
                'error' => $e->getMessage(),
                'recipient_id' => $payload['recipient']['id'],
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Parse incoming webhook payload
     *
     * @param array $data
     * @return array
     */
    public function parseWebhookData($data)
    {
        $messages = [];

        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['messaging'])) {
                    foreach ($entry['messaging'] as $messaging) {
                        $messages[] = [
                            'sender_id' => $messaging['sender']['id'] ?? null,
                            'recipient_id' => $messaging['recipient']['id'] ?? null,
                            'timestamp' => $messaging['timestamp'] ?? null,
                            'message' => $messaging['message'] ?? null,
                            'postback' => $messaging['postback'] ?? null,
                            'quick_reply' => $messaging['message']['quick_reply'] ?? null
                        ];
                    }
                }
            }
        }

        return $messages;
    }

    /**
     * Get user profile information
     *
     * @param string $userId
     * @return array|false
     */
    public function getUserProfile($userId)
    {
        try {
            $response = $this->client->get("https://graph.facebook.com/v18.0/{$userId}", [
                'query' => [
                    'fields' => 'first_name,last_name,profile_pic',
                    'access_token' => $this->accessToken
                ]
            ]);

            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {
            Log::error('Failed to get user profile', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}