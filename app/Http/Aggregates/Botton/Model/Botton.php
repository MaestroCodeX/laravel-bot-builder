<?php namespace App\Http\Aggregates\Botton\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Aggregates\Bot\Model\Bot;


class Botton extends Eloquent
{
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'bottons';

    protected $fillable = ['id','parent_id','name','bot_id','position'];

    protected $dates = ['deleted_at'];


    public function bot()
    {
        return $this->belongsTo(Bot::class,'bot_id','id');
    }


    public function child()
    {
        return $this->hasMany(self::class,'parent_id','id');
    }
   
}
