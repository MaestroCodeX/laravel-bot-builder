<?php namespace App\Http\Aggregate\Botton\Repository;

use App\Http\Aggregates\Botton\Model\Botton;
use App\Http\Aggregates\Botton\Contract\BotContract;

class BottonRepository implements BottonContract
{

    public function parentBottonList($bot)
    {
        return Botton::where('bot_id',$bot->id)->whereNull('parent_id')->groupBy('position')->with('bot')->get();
    }


}