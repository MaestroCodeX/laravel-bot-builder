<?php namespace App\Http\Aggregates\Bot\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bot extends Eloquent
{
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'bots';

    protected $fillable = ['id','user_id','name','bot_id','username','token','description'];

    protected $dates = ['deleted_at'];
   
}
