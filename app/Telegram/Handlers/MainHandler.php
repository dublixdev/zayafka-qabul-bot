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
        
        if (Cache::has('saqla')) {
            $data = Cache::get('saqla');
            try {
                $bot->copyMessage(
                    $userId,
                    $data['from_chat_id'],
                    $data['message_id'],
                    reply_markup: $data['reply_markup'] ?? null,
                );
            } catch (\Exception $e) {
                return;
            }
        } else {
            $bot->sendMessage('👋 Assalomu alaykum! ' . $bot->user()->first_name . '! Botimizga xush kelibsiz! 🌟');
        }

        try {
            User::firstOrCreate(['user_id' => $userId]);
        } catch (\Exception $e) {}
    }

    public function admin(Nutgram $bot)
    {
        $bot->sendMessage(
            text: "<b>🛠️ Administrator Paneliga Xush Kelibsiz!</b>\n\n"
                ."Quyidagi buyruqlar bilan ishlashingiz mumkin:\n\n"
                ."📩 /send - Barcha a'zolarga xabar yuborish\n"
                ."👥 /chat_join - Kanal so'rovlarini boshqarish\n"
                ."💾 /saqla - Reklama postini saqlash\n"
                ."📊 /stat - Bot statistikasi\n\n"
                ."⚠️ <i>Barcha operatsiyalar navbat tizimi orqali amalga oshiriladi</i>",
            parse_mode: ParseMode::HTML,
        );
    }

    public function stat(Nutgram $bot)
    {
        $count = User::count();
        $bot->sendMessage(
            text: "📊 Bot a'zolari soni: " . number_format($count),
        );
    }

    public function saqla(Nutgram $bot)
    {
        $message = $bot->message();

        if (!$message->reply_to_message) {
            $bot->sendMessage(
                text: "⚠️ Iltimos, saqlash uchun xabarga reply qiling!",
            );
            return;
        }

        $replyMessage = $message->reply_to_message;
        $data = [
            'from_chat_id' => $bot->userId(),
            'message_id' => $replyMessage->message_id,
            'reply_markup' => $replyMessage->reply_markup?->toArray(),
        ];

        Cache::forever('saqla', $data);

        $this->start($bot);

        $bot->sendMessage(
            text: "✅ Reklama muvaffaqiyatli saqlandi!\n"
                ."🗑️ /remove - Saqlangan reklamani o'chirish",
        );
    }

    public function remove(Nutgram $bot)
    {
        Cache::forget('saqla');
        $bot->sendMessage(
            text: "✅ Reklama muvaffaqiyatli o'chirildi!",
        );
    }

    public function send(Nutgram $bot)
    {
        $message = $bot->message();

        if (!$message->reply_to_message) {
            $bot->sendMessage(
                text: "⚠️ Xabar yuborish uchun habarga reply qiling!",
            );
            return;
        }

        $replyMessage = $message->reply_to_message;
        $data = [
            'from_chat_id' => $bot->userId(),
            'message_id' => $replyMessage->message_id,
            'reply_markup' => $replyMessage->reply_markup?->toArray(),
        ];

        Cache::forever('send_message', 0);

        $batchSize = 15;
        $delaySeconds = 0;

        User::query()->chunk($batchSize, function ($batch) use (&$delaySeconds, $data) {
            SendMessageJob::dispatch($batch, $data)
                ->delay(now()->addSeconds($delaySeconds));
            $delaySeconds += 1;
        });

        $bot->sendMessage(
            text: "🚀 Xabar yuborish jarayoni boshlandi!\n"
                ."📈 /stat_send - Xabar yuborish statistikasi",
        );
    }

    public function chat_join(Nutgram $bot)
    {
        $count = ChatJoinRequest::count();
        $bot->sendMessage(
            text: "<b>📥 Kanal So'rovlari Boshqaruvi</b>\n\n"
                ."🔹 Joriy navbatdagi so'rovlar: " . number_format($count) . " ta\n"
                ."🔹 Quyidagi buyruqlar bilan ishlashingiz mumkin:\n\n"
                ."✅ /boshlash - Barcha so'rovlarni tasdiqlash\n"
                ."🎯 /boshlash_kanal [Kanal ID] - Maxsus kanal so'rovlari\n\n"
                ."<i>ℹ️ Misol: <code>/boshlash_kanal -1001234567890</code></i>\n\n"
                ."⚠️ <i>Jarayon boshlangandan keyin keladigan so'rovlar avtomatik qo'shilmaydi</i>",
            parse_mode: ParseMode::HTML,
        );
    }

    public function boshlash_kanal(Nutgram $bot, int $chat_id)
    {
        $requests = ChatJoinRequest::where('chat_id', $chat_id);

        if (!$requests->exists()) {
            $bot->sendMessage(
                text: "❌ Ushbu kanalda so'rovlar topilmadi!",
            );
            return;
        }

        $batchSize = 20;
        $delaySeconds = 0;

        $requests->chunk($batchSize, function ($batch) use (&$delaySeconds) {
            ChatJoinRequestJob::dispatch($batch)
                ->delay(now()->addSeconds($delaySeconds));
            $delaySeconds += 1;
        });

        $bot->sendMessage(
            text: "✅ Kanal so'rovlari qabul qilish boshlandi!\n"
                ."📊 /stat_zayafka - Jarayon statistikasi",
        );
    }

    public function boshlash(Nutgram $bot)
    {
        $batchSize = 20;
        $delaySeconds = 0;

        ChatJoinRequest::query()->chunk($batchSize, function ($batch) use (&$delaySeconds) {
            ChatJoinRequestJob::dispatch($batch)
                ->delay(now()->addSeconds($delaySeconds));
            $delaySeconds += 1;
        });

        $bot->sendMessage(
            text: "✅ Barcha so'rovlarni qabul qilish jarayoni boshlandi!\n"
                ."📊 /stat_zayafka - Statistikani ko'rish",
        );
    }

    public function stat_send(Nutgram $bot)
    {
        $count = Cache::get('send_message', 0);
        $bot->sendMessage(
            text: "📨 Yuborilgan xabarlar soni: " . number_format($count),
        );
    }

    public function stat_zayafka(Nutgram $bot)
    {
        $count = ChatJoinRequest::count();
        $bot->sendMessage(
            text: "📥 Qolgan so'rovlar soni: " . number_format($count),
        );
    }

    public function webhook(Nutgram $bot)
    {
        $bot->run();
    }
}
