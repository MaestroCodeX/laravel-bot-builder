<?php   namespace App\Http\Aggregates\User\Controller;

use Telegram;
use Telegram\Bot\Api;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Http\Aggregates\Bot\Controller\BotController;
use App\Http\Aggregates\Bot\Contract\BotContract as Bot;
use App\Http\Aggregates\Start\Controller\StartController;
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
                        return $this->buttons($bot,$value['message']);                 
                    default:
                        return $this->notFound($value['message']);
                }
            }            
    }



    public function start($message)
    {

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




    public function buttons($bot,$message)
    {
        $bottons = $this->botton->parentBottonList($bot);

        foreach($bottons as $botton)
        {

        }

        // [trans('start.newBouttonKey')." 0"],

        $keyboard = [  
            [trans('start.PreviusBtn')]
        ];


        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        
        $html = "
            <i>بخش مدیریت دکمه ها</i>
        ";
        
        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }

    

}