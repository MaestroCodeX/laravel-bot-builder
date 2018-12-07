<?php namespace App\Http\Aggregate\Botton\Repository;

use App\Http\Aggregates\Botton\Model\BotChannel;
use App\Http\Aggregates\Botton\Model\Botton;
use App\Http\Aggregates\Botton\Model\BottonData;
use App\Http\Aggregates\Botton\Contract\BottonContract;

class BottonRepository implements BottonContract
{


    public function createBottonData($data)
    {
        return BottonData::create($data);
    }

    public function updateBottonData($bot_id,$botton_id,$sort)
    {
        return BottonData::where('bot_id',$bot_id)->where('botton_id',$botton_id)->update(['sort'=>$sort]);
    }

    public function bottonData($bot_id,$botton_id)
    {
        $BottonData = BottonData::where('bot_id',$bot_id)->where('botton_id',$botton_id)->first();
        return BottonData::where('bot_id',$bot_id)->where('botton_id',$botton_id)->orderBy('created_at',$BottonData->sort)->get();
    }

    public function updateFileCaption($fileID,$text)
    {
        return BottonData::where("fileID",'=',$fileID)->update(["caption"=>$text]);
    }
  
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
            return Botton::where('bot_id',$bot_id)->whereNull('parent_id')->where('name','=',$text)->with('bot')->first();
        }
        return Botton::where('bot_id',$bot_id)->where('parent_id',$parent_id)->where('name','=',$text)->with('bot')->first();
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


    public function createBotChannel($botId,$username)
    {
        return BotChannel::updateOrCreate(['bot_id'=> $botId],[
            'bot_id'=> $botId,
            'username' => $username
        ]);
    }

    public function getChannelBot($botID)
    {
        return BotChannel::where('bot_id',$botID)->first();
    }

    public function deleteChannelBot($botID)
    {
        return BotChannel::where('bot_id',$botID)->delete();
    }

    public  function updateBotChannelText($botId,$text)
    {
        return BotChannel::where('bot_id',$botId)->update(['message'=>$text]);
    }

}
