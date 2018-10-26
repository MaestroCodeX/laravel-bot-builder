<?php   namespace App\Http\Aggregates\Botton\Controller;

use Telegram;
use Telegram\Bot\Api;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Aggregates\User\Controller\UserController;
use App\Http\Aggregates\User\Contract\UserContract as User;
use App\Http\Aggregates\AdminBot\Controller\AdminBotController;
use App\Http\Aggregates\Botton\Contract\BottonContract as Botton;

class BottonController extends Controller
{

    private $user;
    private $botton;

    public function __construct(Botton $botton, User $user)
    {
        $this->botton = $botton;
        $this->user = $user;
    }


    



   
}