<?php

namespace App\Console\Commands;

use App\Models\Poll;
use App\Services\PollNotificationService;
use Illuminate\Console\Command;

class SendPollNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:send-poll-notification {poll_uuid?} {--latest}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send poll notification to all players via Facebook Messenger';

    private $notificationService;

    public function __construct(PollNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pollUuid = $this->argument('poll_uuid');
        $useLatest = $this->option('latest');

        if ($useLatest) {
            $poll = Poll::getLatestOpenPoll();
            if (!$poll) {
                $this->error('No open polls found.');
                return 1;
            }
        } elseif ($pollUuid) {
            $poll = Poll::where('uuid', $pollUuid)->first();
            if (!$poll) {
                $this->error("Poll with UUID {$pollUuid} not found.");
                return 1;
            }
        } else {
            $this->error('Please provide a poll UUID or use --latest option.');
            return 1;
        }

        $this->info("Sending poll notification for: " . $poll->poll_date->format('Y-m-d'));
        
        $results = $this->notificationService->notifyPlayersAboutNewPoll($poll);

        $this->info("Notification sent to {$results['successful_notifications']} players.");
        
        if ($results['failed_notifications'] > 0) {
            $this->warn("Failed to send to {$results['failed_notifications']} players:");
            foreach ($results['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        return 0;
    }
}
