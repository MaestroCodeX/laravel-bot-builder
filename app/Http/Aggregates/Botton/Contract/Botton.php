<?php namespace App\Http\Aggregates\Botton\Contract;

interface  BottonContract
{
    public function createBotton($data);

    public function bottonList($bot,$parent_id);

    public function existParentBtn($text,$bot_id,$user_id,$parent_id=null);

    public function updateBtn($botton_id,$name);

    public function deleteBtn($botton_id);

    public function updatePosition($botton_id,$position);

}