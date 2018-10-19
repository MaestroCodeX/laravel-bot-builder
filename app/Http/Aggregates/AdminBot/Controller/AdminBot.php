<?php   namespace App\Http\Aggregates\AdminBot\Controller;

use Telegram\Bot\Api;
use App\Http\Controllers\Controller;
use App\Http\Aggregates\Bot\Controller\BotController;
use App\Http\Aggregates\User\Controller\UserController;
use App\Http\Aggregates\Bot\Contract\BotContract as Bot;
use App\Http\Aggregates\Start\Controller\StartController;
use App\Http\Aggregates\User\Contract\UserContract as User;
use Telegram;

class AdminBotController extends Controller
{


    private $user;
    private $bot;

    public function __construct(User $user, Bot $bot)
    {
        $this->user = $user;
        $this->bot = $bot;
    }


    public function AdminBot($updates)
    {
                    $value = $updates;

                    if(isset($value['message']['text']))
                    {

                        // validate user token with sms
                        if(is_numeric($value['message']['text']))
                        {   
                            return app(UserController::class)->checkAndActiveUser($value['message']);
                        }
                        // get botfather token with exact token
                        if(strlen($value['message']['text']) >= 35 && strlen($value['message']['text']) < 150)
                        {
                            return app(BotController::class)->validateBotWithToken($value);
                        }
                        // get botfather token with forwarded text in botfather
                        if(strlen($value['message']['text']) > 150)
                        {
                            return app(BotController::class)->validateBotWithTokenText($value);
                        }
                        //register user with their contact
                        if(isset($value['message']['contact']))
                        {
                            return app(UserController::class)->register($value['message']);
                        }   


                        switch($value['message']['text'])
                        {
                            case trans('start.StartBot'):
                                return $this->start($value['message']);
                            case trans('start.PreviusBtn'):
                                return $this->start($value['message']);
                            case trans('start.NewBot'):
                                return app(BotController::class)->newBot($value['message']);
                            case trans('start.MyBots'):
                                return app(BotController::class)->myBots($value['message']);
                            case trans('start.repeatSms'):
                                return app(UserController::class)->repeatSms($value['message']);
                            case trans('start.createBotContinue'):
                                return app(BotController::class)->createBot($value['message']);
                            case strpos($value['message']['text'],'@') === 0:
                                return app(BotController::class)->BotAction($value['message']);
                            case trans('start.deleteBot'):
                                return app(BotController::class)->deleteBot($value['message']);
                            default:
                                return $this->notFound($value['message']);
                        }
                    }
         

    }
    





    public function start($message)
    {

        $keyboard = [
            [trans('start.Rules')],
            [trans('start.NewBot')],
            [trans('start.ReportAbuse'), trans('start.MyBots')],
            [trans('start.Help'),trans('start.SendComment')],
        ];
        
        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);

        $html = "
            <i>با سلام</i>
            <i>با استفاده از این ربات میتوانید😃👇</i>

            <i>1-</i><code> بدون نیاز به برنامه نویسی، ربات خود را بسازید.</code>
            <i>2-</i><code> به تمامی اعضا ربات پیام ارسال کنید.</code>
            <i>3-</i><code> لیست اعضا ربات خود را دانلود کنید.</code>
            <i>4-</i><code> با تک تک اعضا ربات گفت و گو کنید.</code>
            <i>5-</i><code> عضویت اجباری برای کانال خودتان در ربات طراحی کنید.</code>
            <i>6-</i><code> برای ربات دکمه با مطالب و طراحی دلخواه ایجاد کنید.</code>
            <i>7-</i><code> برای ربات پاسخ خودکار به متن کاربران طراحی کنید.</code>
            <i>8-</i><code> آمار اعضای خود را به صورت نمودار برسی کنید.</code>
            <i>9-</i><code> برای ربات خود عکس پروفایل و متن توضیحات اضافه کنید.</code>
            <i>10-</i><code> و از تمامی امکانات ربات لذت ببرید... !</code>

            <i>برای شروع روی دکمه 'ساخت ربات جدید' کلیک کنید</i>
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
            [trans('start.Rules')],
            [trans('start.NewBot')],
            [trans('start.ReportAbuse'), trans('start.MyBots')],
            [trans('start.Help'),trans('start.SendComment')],
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






}