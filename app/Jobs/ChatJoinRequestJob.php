<?php

namespace App\Jobs;

use App\Models\ChatJoinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ChatJoinRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $chat_joins;

    /**
     * Create a new job instance.
     */

public function __construct($chat_joins)
{
        $this->chat_joins = $chat_joins;
}

    /**
     * Execute the job.
     */
public function handle(): void
{

$responses = Http::pool(function ($pool)
{
    $requests = [];
    foreach ($this->chat_joins as $chat_join)
    {
        $url = 'https://api.telegram.org/bot' . config('nutgram.token') . '/approveChatJoinRequest';

        $requests[] = $pool->post($url, [
            'chat_id' => $chat_join->chat_id,
            'user_id' => $chat_join->user_id,
        ]);
    }
    return $requests;
});

$deleteIds = [];
        // Process responses using the index to map back to chat join IDs
        foreach ($responses as $index => $response)
        {
            if ($response->successful())
            {
                $json = $response->json();
                if (isset($json['ok']) && $json['ok']) {
                    // Use the index to get the correct chat join ID from $this->chat_joins
                    $deleteIds[] = $this->chat_joins[$index]->id;
                }
            }
        }

        // Delete the successfully processed chat join requests
        if (!empty($deleteIds))
        {
            ChatJoinRequest::whereIn('id', $deleteIds)->delete();
        }

}

}
