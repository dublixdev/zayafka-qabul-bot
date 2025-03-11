<?php

namespace App\Telegram\Handlers;

use App\Jobs\ChatJoinRequestJob;
use App\Jobs\SendMessageJob;
use App\Models\ChatJoinRequest;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Cache;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class MainHandler
{
    public function start(Nutgram $bot): void
    {
        $userId = $bot->userId();
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

        } else {
            $bot->sendMessage('Привет! '. $bot->user()->first_name);
        }

        try {  User::create(['user_id' => $userId]); } catch (\Exception $e) {}

}

public function admin(Nutgram $bot)
{
$bot->sendMessage(
text: "Admin panelga hush kelibsiz bratim!

/send - Azolarga habar yuborish
/chat_join - Zayafkalarni qabul qilish!
/saqla - Reklamani saqlash
/stat - Azolar soni

⚠️ Barcha tizimlar Job orqali ishlaydi",
            parse_mode: ParseMode::HTML,
);
}

public function stat(Nutgram $bot)
{
    $count = User::count();
    $bot->sendMessage(
        text: "Azolar soni: $count",
    );
}

public function saqla(Nutgram $bot)
{

    $message = $bot->message();

    if(!$message->reply_to_message)
    {
        $bot->sendMessage(
            text: "Biron bir habarni reply qilishingiz kerak!!!",
        );
        return;
    }

    $replyMessage = $message->reply_to_message;
    $message_id = $replyMessage->message_id;
    $reply_markup = $replyMessage->reply_markup;

    $data = [];
    $data['from_chat_id'] = $bot->userId();
    $data['message_id'] = $message_id;
    $data['reply_markup'] = $reply_markup ? json_encode($reply_markup->toArray()) : false;

    Cache::forever('saqla', $data);

    $this->start($bot);

    $bot->sendMessage(
        text: "Reklama saqlandi!

/remove - reklamani o'chirish",
    );

}

public function remove(Nutgram $bot)
{

    $bot->sendMessage(
        text: "Reklama o'chirildi!",
    );

    Cache::forget('saqla');
}

public function send(Nutgram $bot)
{
    $message = $bot->message();

    if(!$message->reply_to_message)
    {
        $bot->sendMessage(
            text: "Biron bir habarni reply qilishingiz kerak!!!",
        );
        return;
    }

    $replyMessage = $message->reply_to_message;
    $message_id = $replyMessage->message_id;
    $reply_markup = $replyMessage->reply_markup;

    $data = [];
    $data['from_chat_id'] = $bot->userId();
    $data['message_id'] = $message_id;
    $data['reply_markup'] = $reply_markup ? json_encode($reply_markup->toArray()) : false;

Cache::forever('send_message', 0);

$batchSize = 15;
$delaySeconds = 0;

User::query()->chunk($batchSize, function ($batch) use (&$delaySeconds, $data)
{
    SendMessageJob::dispatch($batch, $data)
        ->delay(now()->addSeconds($delaySeconds));

    $delaySeconds += 1;
});

$bot->sendMessage(
    text: "✅ Habar yuborish boshlandi!

/stat_send - Habar yuborish statitikasi",
);

}

public function chat_join(Nutgram $bot)
{
$n = ChatJoinRequest::count();
$bot->sendMessage("<b>📌 Zayafka qabul qilish tartibi:</>

✅ Hozirda ".number_format($n)." ta zayafka mavjud. Agar siz zayafka qabul qilishni boshlasangiz, hozir mavjud barcha zayafkalar avtomatik qabul qilinadi.
<i>⚠️ Qabul qilish boshlanganidan keyin tushadigan yangi zayafkalar bu jarayonga kirmaydi.</>

/boshlash - zayafkalarni qabul qilishni boshlash

💡 Yoki siz faqat belgilangan kanalni zayafkalarini qabul qilishingiz ham mumkin
/boshlash_kanal kanal_id_raqami - Faqat belgilangan kanalni zayafkalarini qabul qiladi

<b>Masalan:</b> /boshlash_kanal -10012345678

",
    parse_mode: ParseMode::HTML,
);

}

public function boshlash_kanal(Nutgram $bot, int $chat_id)
{
    $chat = ChatJoinRequest::where('chat_id', trim($chat_id));

    if($chat->exists())
    {
        $bot->sendMessage(
            text: "❌ Bundan kanaldan zayafka qabul qilmaganman",
        );
        return;
    }

    $batchSize = 20;
    $delaySeconds = 0;

    ChatJoinRequest::where('chat_id', $chat_id)->chunk($batchSize, function ($batch) use (&$delaySeconds)
    {
        ChatJoinRequestJob::dispatch($batch)
            ->delay(now()->addSeconds($delaySeconds));

        $delaySeconds += 1;
    });

$bot->sendMessage(
        text: "✅ Zayafka qabul qilish boshlandi bu kanalda {number_format($chat->count())} ta zayafka bor ekan.

/stat_zayafka - Habar yuborish statitikasi",
);


}

public function boshlash(Nutgram $bot)
{
$batchSize = 20;
$delaySeconds = 0;

ChatJoinRequest::query()->chunk($batchSize, function ($batch) use (&$delaySeconds)
{
    ChatJoinRequestJob::dispatch($batch)
        ->delay(now()->addSeconds($delaySeconds));

    $delaySeconds += 1;
});

$bot->sendMessage(
    text: "✅ Zayafka qabul qilish boshlandi

/stat_zayafka - Habar yuborish statitikasi",
);

}

public function stat_send(Nutgram $bot)
{
    $bot->sendMessage(
        text: "Habar yuborishlar soni: ". number_format(Cache::get('send_message', 0)),
    );
}

public function stat_zayafka(Nutgram $bot)
{
    $n = ChatJoinRequest::count();
    $bot->sendMessage(
        text: "Qolgan zayafkalar soni: ".number_format($n),
    );
}

public function webhook(Nutgram $bot)
{
    $bot->run();
}


}
