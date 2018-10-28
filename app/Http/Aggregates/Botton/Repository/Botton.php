<?php namespace App\Http\Aggregate\Botton\Repository;

use App\Http\Aggregates\Botton\Model\Botton;
use App\Http\Aggregates\Botton\Contract\BottonContract;

class BottonRepository implements BottonContract
{


    public function createBotton($data)
    {
        return Botton::create($data);
    }

    public function bottonList($bot,$parent_id)
    {
        if(is_null($parent_id))
        {
            return Botton::where('bot_id',$bot->id)->whereNull('parent_id')->with('bot')->get();
        }
        return Botton::where('bot_id',$bot->id)->where('parent_id',$parent_id)->with('bot')->get();
    }


    public function existParentBtn($text,$bot_id,$user_id,$parent_id=null)
    {
        if(is_null($parent_id))
        {
            return Botton::where('bot_id',$bot_id)->whereNull('parent_id')->where('name','=',$text)->whereHas('bot', function ($query) use($user_id) {
                $query->whereHas('user', function($q) use($user_id)
                {
                        $q->where('telegram_user_id',$user_id);
                });
            })->with('bot')->first();
        }
        return Botton::where('bot_id',$bot_id)->where('parent_id',$parent_id)->where('name','=',$text)->whereHas('bot', function ($query) use($user_id) {
            $query->whereHas('user', function($q) use($user_id)
            {
                    $q->where('telegram_user_id',$user_id);
            });
        })->with('bot')->first();
    }



    public function updateBtn($botton_id,$name)
    {
        return Botton::where('id',$botton_id)->update(['name'=>$name]);
    }


    public function deleteBtn($botton_id)
    {
        return Botton::where('id',$botton_id)->delete();
    }


    public function updatePosition($botton_id,$position)
    {
        return Botton::where('id',$botton_id)->update(['position'=>$position]);
    }

}