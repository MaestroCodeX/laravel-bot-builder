<?php namespace App\Http\Aggregates\Botton\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Aggregates\Bot\Model\Bot;


class BotUser extends Eloquent
{
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'bot_users';

    protected $fillable = ['username','bot_id','telegram_user_id','first_name','last_name'];

    protected $dates = ['deleted_at'];


    public function bot()
    {
        return $this->belongsTo(Bot::class,'bot_id','id');
    }


   
}
