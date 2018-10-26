<?php namespace App\Http\Aggregate\Botton\Repository;

use App\Http\Aggregates\Botton\Model\Botton;
use App\Http\Aggregates\Botton\Contract\BottonContract;

class BottonRepository implements BottonContract
{


    public function createBotton($data)
    {
        return Botton::create($data);
    }

    public function parentBottonList($bot)
    {
        return Botton::where('bot_id',$bot->id)->whereNull('parent_id')->with('bot')->get();
    }


}