<?php namespace App\Http\Aggregates\User\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Eloquent
{
    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table = 'users';

    protected $fillable = ['id','phone_number','telegram_user_id','name','last_name','user_name','vcard','activation_code'];

    protected $dates = ['deleted_at'];
   
}
