<?php namespace App\Http\Aggregates\Botton\Contract;

interface  BottonContract
{
    public function createBotton($data);

    public function bottonList($bot,$parent_id);

    public function existParentBtn($text,$bot_id,$user_id,$parent_id=null);

}