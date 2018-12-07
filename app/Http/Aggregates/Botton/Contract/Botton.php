<?php namespace App\Http\Aggregates\Botton\Contract;

interface  BottonContract
{
    public function createBottonData($data);

    public function updateBottonData($bot_id,$botton_id,$sort);

    public function bottonData($bot_id,$botton_id);

    public function createBotton($data);

    public function bottonList($bot,$parent_id);

    public function existParentBtn($text,$bot_id,$user_id,$parent_id=null);
 
    public function updateBtn($botton_id,$name);

    public function deleteBtn($botton_id);

    public function updatePosition($botton_id,$position);

    public function updateFileCaption($fileID,$text);

    public function createBotChannel($botId,$username);

    public  function updateBotChannelText($botId,$text);

    public function getChannelBot($botID);

    public function createBotUser($data);

    public function getBotUser($bot_id,$user_id);
}
