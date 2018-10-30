<?php namespace App\Http\Aggregates\Botton\Model;

use App\Http\Aggregates\Bot\Model\Bot;
use App\Http\Aggregates\Botton\Model\Botton;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model as Eloquent;

class BottonData extends Eloquent
{
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'botton_data';

    protected $fillable = ['type','fileID','fileSize','sort','data','bot_id','botton_id'];

    protected $dates = ['deleted_at'];


    public function bot()
    {
        return $this->belongsTo(Bot::class,'bot_id','id');
    }

    public function botton()
    {
        return $this->belongsTo(Botton::class,'botton_id','id');
    }
   
}
