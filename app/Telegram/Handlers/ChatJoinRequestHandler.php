<?php

namespace App\Telegram\Handlers;

use App\Models\ChatJoinRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use SergiX44\Nutgram\Nutgram;

class ChatJoinRequestHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $chatJoin = $bot->chatJoinRequest();
        $userId = $chatJoin->from->id;
        $chatId = $chatJoin->chat->id;

        try { ChatJoinRequest::create(['user_id' => $userId,'chat_id' => $chatId, ]); } catch (\Exception $e) {}

        if(Cache::has('saqla'))
        {
            $data = Cache::get('saqla');

            try {
                $bot->copyMessage(
                    $userId,
                    $data['from_chat_id'],
                    $data['message_id'],
                    reply_markup: $data['reply_markup'] ?? null,
                );
            } catch (\Exception $e)
            {
                return;
            }

        }

      try {  User::create(['user_id' => $userId]); } catch (\Exception $e) {}

    }
}
