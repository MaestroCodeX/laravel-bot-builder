<?php   namespace App\Http\Aggregates\AdminBot\Controller;

use Telegram\Bot\Api;
use App\Http\Controllers\Controller;
use App\Http\Aggregates\Bot\Controller\BotController;
use App\Http\Aggregates\User\Controller\UserController;
use App\Http\Aggregates\Bot\Contract\BotContract as Bot;
use App\Http\Aggregates\Start\Controller\StartController;
use App\Http\Aggregates\User\Contract\UserContract as User;

class AdminBotController extends Controller
{


    private $user;
    private $bot;

    public function __construct(User $user, Bot $bot)
    {
        $this->user = $user;
        $this->bot = $bot;
    }


    public function AdminBot()
    {
        $telegram = new Api(config('telegram.bot_token'));
        // set_time_limit(0);
        // $last_update = 0;
        // while(true)
        // {
            
            $updates = $telegram->getWebhookUpdates();
            dd($updates);

            foreach ($updates as $key => $value) 
            {     
                if($last_update < $value['update_id'])
                {

                    dd($value);
                    $last_update = $value['update_id'];
                    
                    if(isset($value['message']['text']))
                    {

                        // validate user token with sms
                        if(is_numeric($value['message']['text']))
                        {   
                            app(UserController::class)->checkAndActiveUser($telegram,$value['message']);
                            break;
                        }
                        // get botfather token with exact token
                        if(strlen($value['message']['text']) >= 35 && strlen($value['message']['text']) < 150)
                        {
                            app(BotController::class)->validateBotWithToken($value,$telegram);
                            break;
                        }
                        // get botfather token with forwarded text in botfather
                        if(strlen($value['message']['text']) > 150)
                        {
                            app(BotController::class)->validateBotWithTokenText($value,$telegram);
                            break;
                        }
                        //register user with their contact
                        if(isset($value['message']['contact']))
                        {
                            app(UserController::class)->register($telegram,$value['message']);
                            break;
                        }   



                        switch($value['message']['text'])
                        {
                            case trans('start.StartBot'):
                                app(StartController::class)->start($telegram,$value['message']);
                                break;
                            case trans('start.PreviusBtn'):
                                app(StartController::class)->start($telegram,$value['message']);
                                break;
                            case trans('start.NewBot'):
                                app(BotController::class)->newBot($telegram,$value['message']);
                                break;        
                            case trans('start.MyBots'):
                                app(BotController::class)->myBots($telegram,$value['message']);
                                break;  
                            case trans('start.repeatSms'):
                                app(UserController::class)->repeatSms($telegram,$value['message']);
                                break;
                            case trans('start.createBotContinue'):
                                app(BotController::class)->createBot($telegram,$value['message']);
                                break;
                            case strpos($value['message']['text'],'@') === 0:
                                app(BotController::class)->BotAction($telegram,$value['message']);
                                break;    
                            case trans('start.deleteBot'):
                                app(BotController::class)->deleteBot($telegram,$value['message']);
                                break;    
                            default:
                                app(StartController::class)->notFound($telegram,$value['message']);
                                break;
                        }


                        

                    }

                    

                }
            }
        //     sleep(0.1);
        // }
    }
    










}