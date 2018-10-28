<?php   namespace App\Http\Aggregates\User\Controller;

use Telegram;
use Telegram\Bot\Api;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Http\Aggregates\Bot\Controller\BotController;
use App\Http\Aggregates\Bot\Contract\BotContract as Bot;
use App\Http\Aggregates\Start\Controller\StartController;
use App\Http\Aggregates\Botton\Controller\BottonController;
use App\Http\Aggregates\User\Contract\UserContract as User;
use App\Http\Aggregates\Botton\Contract\BottonContract as Botton;


class UserController extends Controller
{


    private $user;
    private $bot;
    private $botton;
    
    public function __construct(Botton $botton, User $user, Bot $bot)
    {
        $this->user = $user;
        $this->bot = $bot;
        $this->botton = $botton;
    }



    public function AdminUserBot($bot,$updates)
    {
            $value = $updates;
            if(isset($value['message']['text']))
            {

                $btnActionCacheKey = $value['message']['chat']['id'].'_bottonAction';    
                if(Cache::has($btnActionCacheKey))
                {   
                    $cacheGet = Cache::get($btnActionCacheKey);
                    $btnInfo = json_decode($cacheGet);
                }
                $parentId = (isset($btnInfo) && !empty($btnInfo)) ? $btnInfo[0] : null;

                $alert = $value['message']['chat']['id'].'_botAlert';    
                if(Cache::has($alert))
                {   
                    $alertGet = Cache::get($alert);
                }
                $action = (isset($alertGet) && !empty($alertGet)) ? $alertGet : null;

                $botton = $this->botton->existParentBtn($value['message']['text'],$bot->id,$value['message']['chat']['id'],$parentId);

                switch($value['message']['text'])
                {
                    case trans('start.StartBot'):
                        return $this->start($value['message']);
                    case trans('start.PreviusBtn'):
                        return $this->start($value['message']);
                    case trans('start.report'):
                        return $this->report($bot,$value['message']);  
                    case trans('start.tools'):
                        return $this->tools($value['message']);       
                    case trans('start.publicMessage'):
                        return $this->publicMessage($value['message']);
                    case trans('start.buttons'):
                        return app(BottonController::class)->buttons($bot,$value['message'],null);    
                    case strpos($value['message']['text'],trans('start.newBouttonKey')) === 0:
                        return app(BottonController::class)->newBottonName($bot,$value['message']);    
                    case Cache::has($value['message']['chat']['id'].'_bottonName') :
                        return app(BottonController::class)->insertNewParrentbotton($bot,$value['message']);    
 
                    case trans('start.bottonSubMenu'):
                        return app(BottonController::class)->buttons($bot,$value['message'],Cache::get($value['message']['chat']['id'].'_bottonAction'));
                    
                    case trans('start.bottonChangePosition'):
                        return app(BottonController::class)->getChangePosition($bot,$value['message'],$botton);          
                    case $action == 'poistionChanged':
                        return app(BottonController::class)->changePosition($bot,$value['message'],$botton);


                    case trans('start.deleteBotton'):
                        return app(BottonController::class)->deleteBotton($bot,$value['message'],$botton); 
                        
                    case trans('start.editBottonName'):
                        return app(BottonController::class)->getEditBotton($bot,$value['message'],$botton); 
                    case $action == 'editted':
                        return app(BottonController::class)->editBotton($bot,$value['message'],$botton); 
                    case !is_null($botton):
                        return app(BottonController::class)->bottonActions($bot,$value['message'],$botton);        
                    default:
                        return $this->notFound($value['message']);
                }
            }            
    }



    public function start($message)
    {
        $cacheKey = $message['chat']['id'].'_bottonName';
        $btnActionCacheKey = $message['chat']['id'].'_bottonAction';   
        $cacheKeys = $message['chat']['id'].'_botAlert';    
        if(Cache::has($cacheKey))
        {   
            Cache::forget($cacheKey);
        }
        if(Cache::has($btnActionCacheKey))
        {   
            Cache::forget($btnActionCacheKey);
        }
        if(Cache::has($cacheKeys))
        {   
            Cache::forget($cacheKeys);
        }
        $keyboard = [
            [trans('start.buttons'),trans('start.tools')],
            [trans('start.report'),trans('start.publicMessage')]
        ];
        
        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);

        $html = "
            <i>به پنل مدیریت ربات خوش آمدید.</i>
        ";

         return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
        
    }
 

    public function notFound($message)
    {
        $cacheKey = $message['chat']['id'].'_bottonName';
        $btnActionCacheKey = $message['chat']['id'].'_bottonAction';    
        if(Cache::has($cacheKey))
        {   
            Cache::forget($cacheKey);
        }
        if(Cache::has($btnActionCacheKey))
        {   
            Cache::forget($btnActionCacheKey);
        }
        $keyboard = [
            [trans('start.buttons'),trans('start.tools')],
            [trans('start.report'),trans('start.publicMessage')]
        ];
        
        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);

        $html="
            <b>خطا</b>
            <code>دستور ارسالی وجود ندارد</code>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
        
    }


    

    public function report($bot,$message)
    {
        $users = $this->user->botUsersList($bot);
        $usersCount = $this->user->botUsersListCount($bot);

        $keyboard = [  
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        if($usersCount > 0)
        {
            foreach($users as $user)
            {
                $info = $user['username'] ?? $user["phone_number"] ?? null;
                $usersInfo[] = "<i>".$info."<i>";
            }
            $html = "
                <i>لیست کاربران :</i>
                <i>تعداد اعضا : ".$usersCount."</i>
                <i>لیست کاربران جدید : </i>
                ".$usersInfo."
            ";
        }
        else
        {
            $html = "
            <i>لیست کاربران :</i>
            <i>تعداد اعضا : ".$usersCount."</i>
            <i>لیست کاربران جدید : </i>
            ";
        }
        
        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }




  

    

}