<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NewPostNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendNotificationChunk implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $userIds,
        public int $postId)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('SendNotificationChunk job started for user IDs: ' . implode(', ', $this->userIds));
        $users = User::query()->whereKey($this->userIds)->get();
        foreach ($users as $user) {
            sleep(1);
            $user->notify(new NewPostNotification($user->id, $this->postId));
            Log::info('Notification sent to user ID: ' . $user->id);
        }
    }
}
