<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Telegram\Handlers\ChatJoinRequestHandler;
use App\Telegram\Handlers\MainHandler;
use SergiX44\Nutgram\Nutgram;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->onCommand('start',  [MainHandler::class, 'start']);

/*
|--------------------------------------------------------------------------
| Admin Handlers
|--------------------------------------------------------------------------
*/

$bot->group(function (Nutgram $bot)
{
    $bot->onCommand('admin', [MainHandler::class, 'admin']);
    $bot->onCommand('saqla', [MainHandler::class, 'saqla']);
    $bot->onCommand('remove', [MainHandler::class, 'remove']);
    $bot->onCommand('stat', [MainHandler::class,'stat']);
    $bot->onCommand('send', [MainHandler::class,'send']);
    $bot->onCommand('chat_join', [MainHandler::class, 'chat_join']);
    $bot->onCommand('stat_send', [MainHandler::class,'stat_send']);
    $bot->onCommand('boshlash', [MainHandler::class,'boshlash']);
    $bot->onCommand('boshlash_kanal {chat_id}', [MainHandler::class,'boshlash_kanal']);
    $bot->onCommand('stat_zayafka', [MainHandler::class,'stat_zayafka']);
})
->middleware(function (Nutgram $bot, $next)
{
    if(in_array($bot->userId(), config('nutgram.config.admins'))){
        $next($bot);
        return;
    }
    return;

});


/*
|--------------------------------------------------------------------------
| Custom Handlers
|--------------------------------------------------------------------------
*/

$bot->onChatJoinRequest(ChatJoinRequestHandler::class);
