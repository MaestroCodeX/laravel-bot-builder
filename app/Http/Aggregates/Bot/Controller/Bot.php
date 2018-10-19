<?php   namespace App\Http\Aggregates\Bot\Controller;

use Telegram;
use Telegram\Bot\Api;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Aggregates\User\Controller\UserController;
use App\Http\Aggregates\Bot\Contract\BotContract as Bot;
use App\Http\Aggregates\User\Contract\UserContract as User;
class BotController extends Controller
{

    private $user;
    private $bot;

    public function __construct(Bot $bot, User $user)
    {
        $this->bot = $bot;
        $this->user = $user;
    }


    public function botList()
    {
        return $this->bot->botList();
    }

    public function newBot($message)
    {
        $user = $this->user->get_user($message['chat']['id']);
        if($user !== null && $user->status == 'DEACTIVATE')
        {
            return app(UserController::class)->repeatSms($message);
        }
        if($user !== null && $user->status == 'ACTIVATE')
        {
            return $this->createBot($message);
        }
        $keyboard = [
            [[
                'text' => trans('start.ConfirmID'),
                'request_contact' => true
            ]]
        ];
        
        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>مدیر ربات (شخص محترم شما) موظف به تایید هویت خود با استفاده از SMS قبل از ساخت ربات است.</i>
        <i>برای ادامه کار روی دکمه زیر کلیک کنید.</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
        
    }





    public function createBot($message)
    {
        $user = $this->user->get_user($message['chat']['id']);

        $keyboard = [
            [trans('start.CreateBotVideo')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
        <i>توکن دریافتی ربات خود را از </i><a href='@BotFather'>@BotFather</a><i> ارسال کنید.</i>

        <i>اگر نمیدانید چگونه از بات فادر توکن بگیرید:</i>
        <i>1-</i><code> ربات @BotFather را استارت کنید.</code>
        <i>2-</i><code> دستور /newbot را به بات فادر ارسال کنید.</code>
        <i>3-</i><code> یک نام برای ربات خودتان به بات فادر ارسال کنید.</code>
        <i>4-</i><code>  یک یوزرنیم برای ربات خودتان به بات فادر ارسال کنید. توجه کنید که آخر یوزرنیم باید عبارت bot وجود داشته باشد و حتما از نوع لاتین/انگلیسی باشد.</code>
        <i>5-</i><code> اگر تمام مراحل را درست انجام داده باشید، بات فادر متن طولانی ای به عنوان توکن برای شما ارسال میکند.</code>
        <i>6-</i><code> آن متن طولانی که توکن نامیده میشود را به پی وی رسان (همین ربات) فروارد کنید تا ربات شما ساخته شود.</code>
        
        ";
        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);

    }



    public function checkAndCreateBot($botToken,$botInfo,$message)
    {
        $user = $this->user->get_user($message['chat']['id']);
        if($user == null)
        {   
            app(UserController::class)->userNotFound($message);
            config(['telegram.bot_token' => env('TELEGRAM_BOT_TOKEN')]);
            return 'DONE';
        }

        $bot = $this->bot->getBot($botInfo->getId());
        if($bot !== null)
        {   
            $this->botExist($message);
            config(['telegram.bot_token' => env('TELEGRAM_BOT_TOKEN')]);
            return 'DONE';
        }

        $data = [
            'token' => $botToken,
            'bot_id' => $botInfo->getId(),
            'name' => $botInfo->getFirstName(),
            'username' => $botInfo->getUsername(),
            'user_id' => $user->id
        ];
        $this->bot->createBot($data);

        $newBottelegram = new Api($message['text']);

        $newBottelegram->setWebhook(['url' => config('telegram.webhook_url').$botInfo->getId().'/webhook']);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
        <i>ربات شما با موفقیت ایجاد شد</i>
        ";
        Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);

        $inline_keyboard = json_encode([
            'inline_keyboard'=>[
                [
                    ['text'=>$botInfo->getFirstName(), 'url'=>'https://t.me/'.$botInfo->getUsername()]
                ],
            ]
        ]);
 
        $html1 = "
        <i>برای ورود به ربات روی دکمه زیر کلیک کنید. 😃👇</i>
        ";

         return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html1, 
            'parse_mode' => 'HTML',
            'reply_markup' => $inline_keyboard
        ]);
    }






    public function botNotFound($message)
    {
        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
        <i>توکن بات ارسال شده اشتباه است یا در بات فادر ثبت نشده است</i>
        ";
        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }




    public function botExist($message)
    {
        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
        <i>ربات با توکن ارسالی قبلا ثبت شده است</i>
        ";
        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }



    public function validateBotWithToken($value)
    {
        config(['telegram.bot_token' => $value['message']['text']]);
        $telegramBot = new Api(config('telegram.bot_token'));
        $createdBot = $telegramBot->getMe();
        if($createdBot->toArray() == [])
        {
            $this->botNotFound($value['message']);
            config(['telegram.bot_token' => env('TELEGRAM_BOT_TOKEN')]);
            return 'Done';
        }
        $this->checkAndCreateBot($value['message']['text'],$createdBot,$value['message']);
        config(['telegram.bot_token' => env('TELEGRAM_BOT_TOKEN')]);
        return 'Done';
    }


    public function validateBotWithTokenText($value)
    {
        $text =  explode(' ',substr($value['message']['text'], strpos($value['message']['text'], 'API:')));
        $cleanText = str_replace(["API:","\n","For",'"'],"",$text[0]);
        config(['telegram.bot_token' => $cleanText]);
        $telegramBot = new Api(config('telegram.bot_token'));
        $createdBot = $telegramBot->getMe();
        if($createdBot->toArray() == [])
        {
            $this->botNotFound($value['message']);
            config(['telegram.bot_token' => env('TELEGRAM_BOT_TOKEN')]);
            return 'Done';
        }
        $this->checkAndCreateBot($cleanText,$createdBot,$value['message']);
        config(['telegram.bot_token' => env('TELEGRAM_BOT_TOKEN')]);
        return 'Done';
    }



  
    public function myBots($message)
    {
        $bots = $this->bot->userBots($message['chat']['id']);
        if($bots->toarray() == [])
        {
            $keyboard = [
                [trans('start.PreviusBtn')]
            ];
    
            $reply_markup = Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard, 
                'resize_keyboard' => true, 
                'one_time_keyboard' => false
            ]);
            $html = "
            <code>شما هنوز رباتی نساخته اید</code>
            ";
            return Telegram::sendMessage([
                'chat_id' => $message['chat']['id'],
                'reply_to_message_id' => $message['message_id'], 
                'text' => $html, 
                'parse_mode' => 'HTML',
                'reply_markup' => $reply_markup
            ]);
        }

        foreach($bots as $bot)
        {
            $keys[] = ['@'.$bot['username']];
        }
        array_push($keys,[trans('start.PreviusBtn')]);
        $keyboard = $keys;

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
        <i>برای مشاهده تنظیمات بیشتر </i>
        <i>ربات خود را از طریق دکمه های زیر انتخاب کنید</i>
        ";
        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }



    public function BotAction($message)
    {
        $cacheKey = $message['chat']['id'].'_delete';    
        if(Cache::has($cacheKey))
        {   
            Cache::forget($cacheKey);
        }
        $cacheText = str_replace('@','',$message['text']);
        Cache::put($cacheKey, $cacheText, 30);

        $keyboard = [
            [trans('start.deleteBot')],
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
        <i>چه عملیاتی میخواهید بر روی ربات زیر انجام دهید؟</i>
        <i>از کیبورد زیر انتخاب کنید.</i>
        ";
        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }



    public function deleteBot($message)
    {
        $key = $message['chat']['id'].'_delete';
        if(Cache::has($key))
        {
            $value = Cache::get($key);
            $bot = $this->bot->getBotByName($message['chat']['id'],$value);
            if($bot !== null)
            {
                $this->bot->deleteBot($value);
                Cache::forget($key);
                $keyboard = [
                    [trans('start.PreviusBtn')]
                ];
        
                $reply_markup = Telegram::replyKeyboardMarkup([
                    'keyboard' => $keyboard, 
                    'resize_keyboard' => true, 
                    'one_time_keyboard' => false
                ]);
                $html = "
                <i>ربات شما موفقیت شما حذف شد</i>
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
       
        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
        <i>ربات مورد نظر برای حذف وجود ندارد</i>
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