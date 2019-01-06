<?php namespace App\Http\Aggregates\Bot\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Aggregates\User\Model\User;

class Bot extends Eloquent
{
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'bots';

    protected $fillable = ['user_id','name','bot_id','username','token','description'];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
   
}
