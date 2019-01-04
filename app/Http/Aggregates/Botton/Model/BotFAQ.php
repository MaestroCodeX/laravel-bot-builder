<?php namespace App\Http\Aggregates\Botton\Model;

use App\Http\Aggregates\Bot\Model\Bot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model as Eloquent;

class BotFAQ extends Eloquent
{
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'bot_faq';

    protected $fillable = ['question','answer_type','bot_id'];

    protected $dates = ['deleted_at'];

    public function bot()
    {
        return $this->belongsTo(Bot::class,'bot_id','id');
    }
}
