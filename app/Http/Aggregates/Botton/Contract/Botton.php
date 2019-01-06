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

    public function createFaq($data);

    public function updateQuestion($botID,$question,$type,$bottonID);

    public function updateQuestionName($botID,$text,$bottonID);

    public function listOfFAQ($botID,$bottonID);

    public function deleteAllFAQ($botID,$bottonID);

    public function updateBottonType($bottonID,$type);

    public function getQuestion($questionID);

    public function createAnswer($data);

    public function get_user($user_id);

    public function updateAnswerGroup($count,$user_id,$bottonId);

}
