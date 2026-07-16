<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Notifications\ReactPostNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class NotificationProcess implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $type,
        public int $postId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $post = Post::findOrFail($this->postId);
        if($this->type == 'newPost'){
            Log::info('NotificationProcess job started for new Post');
            User::select('id')->where('lang', $post->lang)
            ->chunkById(2, function ($users) use ($post) {
                    SendNotificationChunk::dispatch($users->pluck('id')->toArray(), $post->id)->onQueue('NewPostNotifications');
                    Log::info('Dispatched SendNotificationChunk job for user IDs: ' . implode(', ', $users->pluck('id')->toArray()));
                }
            );
        }elseif($this->type == 'newReactPost'){
            $user = User::query()->find($post->user_id);
            Log::info('NotificationProcess job started for new react Post');
            $user->notify(new ReactPostNotification($post->id));
            Log::info('Notification sent to user ID: ' . $user->id);
        }
    }
}
