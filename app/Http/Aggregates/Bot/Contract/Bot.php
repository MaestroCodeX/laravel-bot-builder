<?php namespace App\Http\Aggregates\Bot\Contract;

interface  BotContract
{
    public function createBot($data);

    public function getBot($botId);
}