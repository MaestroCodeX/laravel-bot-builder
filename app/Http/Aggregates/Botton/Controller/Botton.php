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


    

    public function buttons($bot,$message, $parent_id = null)
    {
        $bottons = $this->botton->bottonList($bot,$parent_id);
        $groupBottons = $bottons->groupBy('position');

        $encodeBtn = json_encode($groupBottons);
        $decodeBtn = json_decode($encodeBtn,true);
        $keyboards = [];
        foreach($decodeBtn as $key => $gb)
        {
            $btn = array_column($gb,'name');
            array_push($btn,trans('start.newBouttonKey')." ".$key);
            $keyboards[] = $btn;
        }
        $countOfBottons = count($groupBottons)+1;
        array_push($keyboards,[trans('start.newBouttonKey')." ".$countOfBottons]);
        array_push($keyboards,[trans('start.PreviusBtn')]);

        $keyboard = $keyboards;

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





    public function newBottonName($bot,$message)
    {
        $cacheKey = $message['chat']['id'].'_bottonName';    
        if(Cache::has($cacheKey))
        {   
            Cache::forget($cacheKey);
        }
        Cache::put($cacheKey, $message['text'], 30);

        $keyboard = [];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        
        $html = "
            <i>نام دکمه مورد نظر را ارسال کنید</i>,
        ";
        
        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }




    public function insertNewParrentbotton($bot,$message)
    {
        $cacheKey = $message['chat']['id'].'_bottonName';

        $btnActionCacheKey = $message['chat']['id'].'_bottonAction';    
        if(Cache::has($btnActionCacheKey))
        {   
            $parent_id = Cache::get($btnActionCacheKey);
        }
        $parentId = (isset($parent_id) && !empty($parent_id)) ? $parent_id : null;

        $botton = $this->botton->existParentBtn($message['text'],$bot->id,$message['chat']['id'],$parentId);
        if(!is_null($botton))
        {
            if(Cache::has($cacheKey))
            {   
                Cache::forget($cacheKey);
            }
            if(Cache::has($btnActionCacheKey))
            {   
                Cache::forget($btnActionCacheKey);
            }
            $keyboard = [  
                [trans('start.PreviusBtn')]
            ];
    
            $reply_markup = Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard, 
                'resize_keyboard' => true, 
                'one_time_keyboard' => false
            ]);
            
            $html = "
                <b>خطا</b>
                <i>این دکمه تکراری است و دکمه ای با همین عنوان در منو وجود دارد</i>,
            ";
            
            return Telegram::sendMessage([
                'chat_id' => $message['chat']['id'],
                'reply_to_message_id' => $message['message_id'], 
                'text' => $html, 
                'parse_mode' => 'HTML',
                'reply_markup' => $reply_markup
            ]);
        }

        if(Cache::has($cacheKey))
        {
            $value = Cache::get($cacheKey);
            $position = preg_replace("/[^0-9]/", '', $value);
            $data = [
                'parent_id' =>  $parentId,
                'bot_id' => $bot->id,
                'name' =>  $message['text'],
                'position' => $position
            ];
            $this->botton->createBotton($data);
            Cache::forget($cacheKey);
            if(Cache::has($btnActionCacheKey))
            {   
                Cache::forget($btnActionCacheKey);
            }
            $keyboard = [  
                [trans('start.buttons')]
            ];
    
            $reply_markup = Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard, 
                'resize_keyboard' => true, 
                'one_time_keyboard' => false
            ]);
            
            $html = "
                <i>با موفقیت اضافه شد</i>,
            ";
            
            return Telegram::sendMessage([
                'chat_id' => $message['chat']['id'],
                'reply_to_message_id' => $message['message_id'], 
                'text' => $html, 
                'parse_mode' => 'HTML',
                'reply_markup' => $reply_markup
            ]);
        }

        $keyboard = [  
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        
        $html = "
            <i>اشکالی پیش آمده مجددا تلاش کنید برگشت را بزنید</i>,
        ";
        
        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }
   





    public function bottonActions($bot,$message,$botton)
    {
        $keyboard = [  
            [trans('start.editBottonName'),trans('start.bottonAnswer')],
            [trans('start.bottonChangePosition'),trans('start.bottonLink'),trans('start.deleteBotton')],
            [trans('start.bottonSubMenu')],
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        
        $html = "
        <i>بخش مدیریت دکمه '".$message['text']."'</i>
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