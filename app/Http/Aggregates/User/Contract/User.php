<?php namespace App\Http\Aggregates\User\Contract;

interface  UserContract
{
    public function register($telegram_user_id,$data);

    public function update($user_id,$data);

    public function get_user($user_id);

    public function botUsersList($bot);

    public function botUsersListCount($bot);

}