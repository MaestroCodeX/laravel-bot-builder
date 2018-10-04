<?php namespace App\Http\Aggregate\Bot\Repository;

use App\Http\Aggregates\Bot\Model\Bot;
use App\Http\Aggregates\Bot\Contract\BotContract;

class BotRepository implements BotContract
{

    public function createBot($data)
    {
        return Bot::create($data);
    }

    public function getBot($botId)
    {
        return Bot::where('bot_id',$botId)->first();
    }

    public function userBots($user_id)
    {
        return Bot::whereHas('user',function($query) use($user_id) {
            $query->where('telegram_user_id',$user_id);
        })->get();
    }

}