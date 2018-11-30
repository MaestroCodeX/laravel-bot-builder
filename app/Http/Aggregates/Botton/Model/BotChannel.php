<?php namespace App\Http\Aggregates\Botton\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Aggregates\Bot\Model\Bot;


class BotChannel extends Eloquent
{
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'bot_channel';

    protected $fillable = ['username','bot_id'];

    protected $dates = ['deleted_at'];


    public function bot()
    {
        return $this->belongsTo(Bot::class,'bot_id','id');
    }


   
}
