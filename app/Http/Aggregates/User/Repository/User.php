<?php namespace App\Http\Aggregate\User\Repository;

use App\Http\Aggregates\User\Model\User;
use App\Http\Aggregates\User\Contract\UserContract;

class UserRepository implements UserContract
{

    public function register($data)
    {
        return User::create($data);
    }

    public function update($user_id,$data)
    {
        return User::where('telegram_user_id',$user_id)->update($data);
    }

    public function get_user($user_id)
    {
        return User::where('telegram_user_id',$user_id)->first();
    }
}