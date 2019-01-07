<?php namespace App\Http\Aggregates\Botton\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model as Eloquent;

class FAQAnswer extends Eloquent
{
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'user_answers';

    protected $fillable = ['answer','user_id','faq_id','group'];

    protected $dates = ['deleted_at'];

    public function faq()
    {
        return $this->belongsTo(BotFAQ::class,'faq_id','id');
    }

    public function user()
    {
        return $this->belongsTo(BotUser::class,'user_id','telegram_user_id');
    }

}
