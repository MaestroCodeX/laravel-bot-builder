<?php   namespace App\Console\Commands;

use Telegram\Bot\Api;
use Illuminate\Console\Command;
use App\Http\Aggregates\User\Contract\UserContract;
use App\Http\Aggregates\Bot\Controller\BotController;
use App\Http\Aggregates\User\Controller\UserController;
use App\Http\Aggregates\Start\Controller\StartController;
use App\Http\Aggregates\AdminBot\Controller\AdminBotController;

class Telegramcommand extends Command
{

    protected $signature = 'telegram:listen';


    protected $description = 'telegram bot long polling';

    private $user;

    public function __construct(UserContract $user)
    {
        $this->user = $user;
        $this->telegram = new Api(config('telegram.bot_token'));
        parent::__construct();
    }


    public function handle()
    {

    }



}