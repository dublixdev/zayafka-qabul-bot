<?php

namespace App\Jobs;

use App\Models\ChatJoinRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $users;
    protected $data;

    /**
     * Create a new job instance.
     */

public function __construct($users, $data)
{
        $this->users = $users;
        $this->data = $data;
}

/**
     * Execute the job.
*/

public function handle(): void
{
$responses = Http::pool(function ($pool)
{
    $requests = [];
    foreach ($this->users as $user)
    {
        $datas = [];
        $datas['chat_id'] = $user->user_id;
        $datas['from_chat_id'] = $this->data['from_chat_id'];
        $datas['message_id'] = $this->data['message_id'];

        if($this->data['reply_markup'])
        {
            $datas['reply_markup'] = $this->data['reply_markup'];
        }

        $requests[] = $pool->post('https://api.telegram.org/bot' . config('nutgram.token') . '/copyMessage', $datas);
    }
    return $requests;
});

$deleteIds = [];

foreach ($responses as $index => $response)
{
    if ($response->successful()) {
        $json = $response->json();
        if (isset($json['ok']) && $json['ok']) {
            Cache::increment('send_message');
        } else {
            $deleteIds[] = $this->users[$index]->id;
        }
    } else {
        // Handle HTTP error responses as failures
        $deleteIds[] = $this->users[$index]->id;
    }
}

if (!empty($deleteIds))
{
    var_dump($deleteIds);
    User::whereIn('id',$deleteIds)->delete(); //
}

}

}
