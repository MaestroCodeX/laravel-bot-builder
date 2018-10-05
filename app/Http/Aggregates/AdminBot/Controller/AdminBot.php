<?php   namespace App\Http\Aggregates\AdminBot\Controller;

use App\Http\Controllers\Controller;
use App\Http\Aggregates\Bot\Contract\BotContract as Bot;
use App\Http\Aggregates\User\Contract\UserContract as User;
use App\Http\Aggregates\Start\Controller\StartController;
use App\Http\Aggregates\Bot\Controller\BotController;
use App\Http\Aggregates\User\Controller\UserController;

class AdminBotController extends Controller
{


    private $user;
    private $bot;

    public function __construct(User $user, Bot $bot)
    {
        $this->user = $user;
        $this->bot = $bot;
    }


    public function AdminBot($value,$telegram)
    {
       
    }
    
}