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

    public function existBtn($text,$bot_id,$user_id)
    {
        return Botton::where('bot_id',$bot_id)->where('name','=',$text)->whereHas('bot', function ($query) use($user_id) {
            $query->whereHas('user', function($q) use($user_id)
            {
                    $q->where('telegram_user_id',$user_id);
            });
        })->with('bot')->first();
    }


}