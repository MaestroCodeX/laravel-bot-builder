<?php namespace App\Http\Aggregates\Bot\Contract;

interface  BotContract
{
    public function createBot($data);

    public function getBot($botId);

    public function getBotByName($user_id,$username);

    public function userBots($user_id);

    public function deleteBot($value);
}