<?php   namespace App\Http\Aggregates\Start\Controller;

use Telegram;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Http\Aggregates\User\Controller\UserController;
use App\Http\Aggregates\Bot\Contract\BotContract as Bot;
use App\Http\Aggregates\User\Contract\UserContract as User;
use App\Http\Aggregates\AdminBot\Controller\AdminBotController;

class StartController extends Controller
{

    private $user;
    private $bot;

    public function __construct(User $user, Bot $bot)
    {
        $this->user = $user;
        $this->bot = $bot;
    }

    public function init($botID)
    {  
            $bot = $this->bot->getBot($botID);
            if($bot !== null)
            {
                config(['telegram.bot_token' => $bot->token]);
                $updates = Telegram::getWebhookUpdates();
                $message_id = $updates->getMessage()->getFrom()->getId();
                if(isset($bot->user->telegram_user_id) && !empty($bot->user->telegram_user_id) && $bot->user->telegram_user_id == $message_id)
                {
                    return app(UserController::class)->AdminUserBot($bot,$updates);
                }
                return app(UserController::class)->UserBot($bot,$updates);
            }
            $updates = Telegram::getWebhookUpdates();
            return app(AdminBotController::class)->AdminBot($updates);
    }



}
