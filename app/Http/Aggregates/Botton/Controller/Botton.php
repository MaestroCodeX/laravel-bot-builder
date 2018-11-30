<?php   namespace App\Http\Aggregates\Botton\Controller;

use File;
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
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $cacheGet = (isset($parent_id) && !empty($parent_id)) ? json_decode($parent_id) : null;
        $parentId = (isset($cacheGet) && !empty($cacheGet)) ? $cacheGet[0] : null;
        $bottons = $this->botton->bottonList($bot,$parentId);
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
        $cacheKey = $message['chat']['id'].$bot->id.'_bottonName';
        if(Cache::has($cacheKey))
        {
            Cache::forget($cacheKey);
        }
        Cache::put($cacheKey, $message['text'], 40320);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

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
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $cacheKey = $message['chat']['id'].$bot->id.'_bottonName';

        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $parent_id = json_decode($cacheGet);
        }
        $parentId = (isset($parent_id) && !empty($parent_id)) ? $parent_id[0] : null;

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
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        if(!is_null($botton))
        {
            $cacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
            if(Cache::has($cacheKey))
            {
                Cache::forget($cacheKey);
            }
            Cache::put($cacheKey, json_encode([$botton->id,$botton->parent_id]), 40320);
        }

        $keyboard = [
            [trans('start.editBottonName'),trans('start.bottonAnswer')],
            [trans('start.bottonChangePosition'),trans('start.deleteBotton')],
            // [trans('start.bottonChangePosition'),trans('start.bottonLink'),trans('start.deleteBotton')],
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



    public function getEditBotton($bot,$message,$botton)
    {
        $cacheKey = $message['chat']['id'].$bot->id.'_botAlert';
        if(Cache::has($cacheKey))
        {
            Cache::forget($cacheKey);
        }
        Cache::put($cacheKey, 'editted', 40320);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>نام جدید را وارد نمایید</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }


    public function editBotton($bot,$message,$botton)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $parent_id = json_decode($cacheGet);
        }
        $bottonId = (isset($parent_id) && !empty($parent_id)) ? $parent_id[0] : null;

        $this->botton->updateBtn($bottonId,$message['text']);

        $cacheKeys = $message['chat']['id'].$bot->id.'_botAlert';
        if(Cache::has($cacheKeys))
        {
            Cache::forget($cacheKeys);
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
        <i>نام دکمه با موفقیت تغییر کرد</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }




    public function deleteBotton($bot,$message,$botton)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $parent_id = json_decode($cacheGet);
        }
        $bottonId = (isset($parent_id) && !empty($parent_id)) ? $parent_id[0] : null;

        $this->botton->deleteBtn($bottonId);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>دکمه مورد نظر با موفقیت حذف شد</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }




    public function getChangePosition($bot,$message,$botton)
    {
        $cacheKey = $message['chat']['id'].$bot->id.'_botAlert';
        if(Cache::has($cacheKey))
        {
            Cache::forget($cacheKey);
        }
        Cache::put($cacheKey, 'poistionChanged', 40320);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>موقعیت جدید دکمه را به صورت عدد انگلیسی وارد نمایید</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }



    public function changePosition($bot,$message,$botton)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $parent_id = json_decode($cacheGet);
        }
        $bottonId = (isset($parent_id) && !empty($parent_id)) ? $parent_id[0] : null;

        $this->botton->updatePosition($bottonId,$message['text']);

        $cacheKeys = $message['chat']['id'].$bot->id.'_botAlert';
        if(Cache::has($cacheKeys))
        {
            Cache::forget($cacheKeys);
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
        <i>موقعیت دکمه آپدیت شد</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }







    public function bottonAnswerBotton($bot,$message,$botton)
    {
        $keyboard = [
            [trans('start.showArticle'),trans('start.createFaq')],
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>مدیریت نوع پاسخ این دکمه :</i>

        <i>- نمایش مطلب :</i>
        <code>
        مطالب ثبت شده از طرف شما برای این دکمه به انتخاب خودتان تکی یا تصادفی برای کاربر ارسال میشود!
        </code>

        <i>- طراحی فرم سوال</i>
        <code>
        سوال های طراحی شده توسط شما تکی تکی از کاربر پرسیده مشود و ربات بعد از اتمام سوالات پیام ها را برای شما ارسال میکند.
        </code>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }





    public function showArticle‌Botton($bot,$message,$botton)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $botton = json_decode($cacheGet);
        }
        $bottonId = (isset($botton) && !empty($botton)) ? $botton[0] : null;

        $cacheKey = $message['chat']['id'].$bot->id.'_bottonArticle';
        if(Cache::has($cacheKey))
        {
            Cache::forget($cacheKey);
        }
        Cache::put($cacheKey, $bottonId, 40320);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>پیامی که میخواهید این دکمه برای کاربر ارسال کند را ارسال کنید.</i>

        <i>پیام شما میتواند تمام فرمت ها (متن - عکس - ویدیو - فایل - صدا - لوکیشن و ...) باشد.</i>

        <code>
        Parse_Mode = HTML
        </code>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }





    public function getArticle‌Botton($bot,$message)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $cacheKey = $message['chat']['id'].$bot->id.'_bottonArticle';
        if(Cache::has($cacheKey))
        {
            $cacheGet = Cache::get($cacheKey);
        }

        if(isset($message['document']))
        {
            $response = Telegram::getFile(['file_id' => $message['document']['file_id']]);
            if(isset($response['file_path']))
            {
                if (!File::exists(storage_path('files')))
                {
                    File::makeDirectory(storage_path('files'), 0777, true, true);
                }
                $uniqid = $bot->id.'_'.uniqid("document",true);
                $uri = "https://api.telegram.org/file/bot".$bot->token."/".$response['file_path'];
                copy($uri,storage_path('files').'/'.$uniqid.basename($response['file_path']));

                $data = [
                    'type' => 'document',
                    'fileID' => $message['document']['file_id'],
                    'fileSize' => $message['document']['file_size'],
                    'sort' => 'ASC',
                    'data' => storage_path('files').'/'.$uniqid.basename($response['file_path']),
                    'bot_id' => $bot->id,
                    'botton_id' => $cacheGet
                ];
                $this->botton->createBottonData($data);
                $html = "
                <i>خب این مطلب ذخیره شد</i>
                
                <i>اگر قصد اضافه کردن کپشن برای این فایل را دارید روی دکمه 'اضافه کردن کپشن' بزنید</i>
                
                <i> در غیر اینصورت اگر مطلب دیگری میخواهید به این دکمه اضافه کنید را ارسال کنید</i>
                
                <i>  و در غیر این صورت از دکمه اتمام استفاده کنید</i>
                ";
                $cacheKeyCaption = $message['chat']['id'].$bot->id.'_bottonCaption';
                if(Cache::has($cacheKeyCaption))
                {
                    Cache::forget($cacheKeyCaption);
                }
                Cache::put($cacheKeyCaption, $message['document']['file_id'], 40320);
                $keyboard = [
                    [trans('start.addCaption')],
                    [trans('start.doneCreateArticle')],
                    [trans('start.PreviusBtn')]
                ];
            }
        }

        if(isset($message['audio']))
        {
            $response = Telegram::getFile(['file_id' => $message['audio']['file_id']]);
            if(isset($response['file_path']))
            {
                if (!File::exists(storage_path('files')))
                {
                    File::makeDirectory(storage_path('files'), 0777, true, true);
                }
                $uniqid = $bot->id.'_'.uniqid("audio",true);
                $uri = "https://api.telegram.org/file/bot".$bot->token."/".$response['file_path'];
                copy($uri,storage_path('files').'/'.$uniqid.basename($response['file_path']));

                $data = [
                    'type' => 'audio',
                    'fileID' => $message['audio']['file_id'],
                    'fileSize' => $message['audio']['file_size'],
                    'sort' => 'ASC',
                    'data' => storage_path('files').'/'.$uniqid.basename($response['file_path']),
                    'bot_id' => $bot->id,
                    'botton_id' => $cacheGet
                ];
                $this->botton->createBottonData($data);
                $html = "
                    <i>خب این مطلب ذخیره شد</i>
                    <i>اگر مطلب دیگری میخواهید به این دکمه اضافه کنید را ارسال کنید</i>
            
                    <i>در غیر این صورت از دکمه اتمام استفاده کنید</i>
                ";
                $keyboard = [
                    [trans('start.doneCreateArticle')],
                    [trans('start.PreviusBtn')]
                ];
            }
        }

        if(isset($message['video']))
        {
            $response = Telegram::getFile(['file_id' => $message['video']['file_id']]);
            if(isset($response['file_path']))
            {
                if (!File::exists(storage_path('files')))
                {
                    File::makeDirectory(storage_path('files'), 0777, true, true);
                }
                $uniqid = $bot->id.'_'.uniqid("video",true);
                $uri = "https://api.telegram.org/file/bot".$bot->token."/".$response['file_path'];
                copy($uri,storage_path('files').'/'.$uniqid.basename($response['file_path']));

                $data = [
                    'type' => 'video',
                    'fileID' => $message['video']['file_id'],
                    'fileSize' => $message['video']['file_size'],
                    'sort' => 'ASC',
                    'data' => storage_path('files').'/'.$uniqid.basename($response['file_path']),
                    'bot_id' => $bot->id,
                    'botton_id' => $cacheGet
                ];
                $this->botton->createBottonData($data);
                $html = "
                    <i>خب این مطلب ذخیره شد</i>
                    <i>اگر مطلب دیگری میخواهید به این دکمه اضافه کنید را ارسال کنید</i>
            
                    <i>در غیر این صورت از دکمه اتمام استفاده کنید</i>
                ";
                $keyboard = [
                    [trans('start.doneCreateArticle')],
                    [trans('start.PreviusBtn')]
                ];
            }
        }

        if(isset($message['photo']))
        {

            $photo = last(last($message['photo']));

            $response = Telegram::getFile(['file_id' => $photo['file_id']]);
            if(isset($response['file_path']))
            {
                if (!File::exists(storage_path('files')))
                {
                    File::makeDirectory(storage_path('files'), 0777, true, true);
                }
                $uniqid = $bot->id.'_'.uniqid("photo",true);
                $uri = "https://api.telegram.org/file/bot".$bot->token."/".$response['file_path'];
                copy($uri,storage_path('files').'/'.$uniqid.basename($response['file_path']));

                $data = [
                    'type' => 'image',
                    'fileID' => $photo['file_id'],
                    'fileSize' => $photo['file_size'],
                    'sort' => 'ASC',
                    'data' => storage_path('files').'/'.$uniqid.basename($response['file_path']),
                    'bot_id' => $bot->id,
                    'botton_id' => $cacheGet
                ];
                $this->botton->createBottonData($data);

                $html = "
                <i>خب این مطلب ذخیره شد</i>
                
                <i>اگر قصد اضافه کردن کپشن برای این تصویر را دارید روی دکمه 'اضافه کردن کپشن' بزنید</i>
                
                <i> در غیر اینصورت اگر مطلب دیگری میخواهید به این دکمه اضافه کنید را ارسال کنید</i>
                
                <i>  و در غیر این صورت از دکمه اتمام استفاده کنید</i>
                ";
                $cacheKeyCaption = $message['chat']['id'].$bot->id.'_bottonCaption';
                if(Cache::has($cacheKeyCaption))
                {
                    Cache::forget($cacheKeyCaption);
                }
                Cache::put($cacheKeyCaption, $photo['file_id'], 40320);
                $keyboard = [
                    [trans('start.addCaption')],
                    [trans('start.doneCreateArticle')],
                    [trans('start.PreviusBtn')]
                ];
            }
        }


        if(isset($message['location']))
        {
                $data = [
                    'type' => 'location',
                    'fileID' => 'location',
                    'fileSize' => null,
                    'sort' => 'ASC',
                    'data' => json_encode($message['location']),
                    'bot_id' => $bot->id,
                    'botton_id' => $cacheGet
                ];
                $this->botton->createBottonData($data);
                $html = "
                    <i>خب این مطلب ذخیره شد</i>
                    <i>اگر مطلب دیگری میخواهید به این دکمه اضافه کنید را ارسال کنید</i>
            
                    <i>در غیر این صورت از دکمه اتمام استفاده کنید</i>
                ";
                $keyboard = [
                    [trans('start.doneCreateArticle')],
                    [trans('start.PreviusBtn')]
                ];
        }


        if(isset($message['text']))
        {
            $data = [
                'type' => 'text',
                'fileID' => 'text',
                'fileSize' => null,
                'sort' => 'ASC',
                'data' => $message['text'],
                'bot_id' => $bot->id,
                'botton_id' => $cacheGet
            ];
            $this->botton->createBottonData($data);
            $html = "
                    <i>خب این مطلب ذخیره شد</i>
                    <i>اگر مطلب دیگری میخواهید به این دکمه اضافه کنید را ارسال کنید</i>
            
                    <i>در غیر این صورت از دکمه اتمام استفاده کنید</i>
              ";
            $keyboard = [
                [trans('start.doneCreateArticle')],
                [trans('start.PreviusBtn')]
            ];
        }

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }


    public function captionMessage($bot,$message)
    {
        $html = "
                    <i>کپشن خود را برای فایل مورد نظر ارسال کنید</i>     
                    
                    <code>حداکثر 1024 کاراکتر</code>       
             ";

        $reply_markup = Telegram::replyKeyboardMarkup([
            'hide_keyboard' => true
        ]);

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }


    public function getFileCaption($bot,$message)
    {
        $cacheKeyCaption = $message['chat']['id'].$bot->id.'_bottonCaption';
        if(Cache::has($cacheKeyCaption))
        {
            $key = Cache::get($cacheKeyCaption);
        }
        $this->botton->updateFileCaption($key,$message['text']);

        Cache::forget($cacheKeyCaption);

        $html = "
                    <i>خب کپشن دخیره شد</i>
                    <i>اگر مطلب دیگری میخواهید به این دکمه اضافه کنید را ارسال کنید</i>
            
                    <i>در غیر این صورت از دکمه اتمام استفاده کنید</i>
              ";
        $keyboard = [
            [trans('start.doneCreateArticle')],
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }



    public function doneCreateArticle($bot,$message)
    {
        $cacheKeyCaption = $message['chat']['id'].$bot->id.'_bottonCaption';
        if(Cache::has($cacheKeyCaption))
        {
            Cache::forget($cacheKeyCaption);
        }
        $keyboard = [
            [trans('start.ascArticleSort'),trans('start.descArticleSort')],
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>پاسخ های این دکمه چگونه ارسال شوند?</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }



    public function ascArticleSort($bot,$message)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $cacheKey = $message['chat']['id'].$bot->id.'_bottonArticle';
        if(Cache::has($cacheKey))
        {
            $cacheGet = Cache::get($cacheKey);
        }

        $this->botton->updateBottonData($bot->id,$cacheGet,'ASC');

        Cache::forget($cacheKey);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>پاسخ ها با موفقیت اضافه شدند</i>
        <i>برای ویرایش پاسخ ها از دکمه ویرایش پاسخ فعلی استفاده کنید</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }



    public function descArticleSort($bot,$message)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $cacheKey = $message['chat']['id'].$bot->id.'_bottonArticle';
        if(Cache::has($cacheKey))
        {
            $cacheGet = Cache::get($cacheKey);
        }

        $this->botton->updateBottonData($bot->id,$cacheGet,'DESC');

        Cache::forget($cacheKey);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>پاسخ ها با موفقیت اضافه شدند</i>
        <i>برای ویرایش پاسخ ها از دکمه ویرایش پاسخ فعلی استفاده کنید</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }




    public function UerBottonActions($bot, $message, $botton)
    {
        $cacheKey = $message['chat']['id'].$bot->id.'_userBottonAction';
        if(Cache::has($cacheKey))
        {
            Cache::forget($cacheKey);
        }
        Cache::put($cacheKey, json_encode([$botton->id,$botton->parent_id]), 40320);

        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
          ]);

        $bottons = $this->botton->bottonList($bot,$botton->id);
        $groupBottons = $bottons->groupBy('position');

        $encodeBtn = json_encode($groupBottons);
        $decodeBtn = json_decode($encodeBtn,true);
        $keyboards = [];
        foreach($decodeBtn as $key => $gb)
        {
            $btn = array_column($gb,'name');
            $keyboards[] = $btn;
        }
        array_push($keyboards,[trans('start.PreviusBtn')]);

        $keyboard = $keyboards;


        $bottonData = $this->botton->bottonData($bot->id,$botton->id);

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <i>".$botton->name."</i>
        ";
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);

        Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);


        foreach($bottonData as $data)
        {
            switch($data['type'])
            {
                case 'image':

                    Telegram::sendChatAction([
                        'chat_id' => $message['chat']['id'],
                        'action' => 'upload_photo'
                    ]);
                    if(isset($data['caption']) && !is_null($data['caption']))
                    {
                        Telegram::sendPhoto([
                            'chat_id' => $message['chat']['id'],
                            'photo' => $data['data'],
                            'caption' => $data['caption']
                        ]);
                    }
                    else
                    {
                        Telegram::sendPhoto([
                            'chat_id' => $message['chat']['id'],
                            'photo' => $data['data']
                        ]);
                    }
                    break;

                case 'video':

                        Telegram::sendChatAction([
                            'chat_id' => $message['chat']['id'],
                            'action' => 'upload_video'
                        ]);
                        Telegram::sendAudio([
                            'chat_id' => $message['chat']['id'],
                            'audio' => $data['data'],
                        ]);
                        break;


                case 'audio':

                    Telegram::sendChatAction([
                        'chat_id' => $message['chat']['id'],
                        'action' => 'upload_audio'
                    ]);
                    Telegram::sendAudio([
                        'chat_id' => $message['chat']['id'],
                        'audio' => $data['data'],
                    ]);
                    break;

                case 'document':

                    Telegram::sendChatAction([
                        'chat_id' => $message['chat']['id'],
                        'action' => 'upload_document'
                    ]);
                    if(isset($data['caption']) && !is_null($data['caption']))
                    {
                        Telegram::sendDocument([
                            'chat_id' => $message['chat']['id'],
                            'document' => $data['data'],
                            'caption' => $data['caption']
                        ]);
                    }
                    else
                    {
                        Telegram::sendDocument([
                            'chat_id' => $message['chat']['id'],
                            'document' => $data['data'],
                        ]);
                    }
                    break;

                case 'location':

                    Telegram::sendChatAction([
                        'chat_id' => $message['chat']['id'],
                        'action' => 'find_location'
                    ]);
                    $location = json_decode($data['data']);
                    Telegram::sendLocation([
                        'chat_id' => $message['chat']['id'],
                        'latitude' => $location->latitude,
	                    'longitude' => $location->longitude,
                    ]);
                    break;

                case 'text':

                    Telegram::sendChatAction([
                        'chat_id' => $message['chat']['id'],
                        'action' => 'typing'
                    ]);
                     Telegram::sendMessage([
                        'chat_id' => $message['chat']['id'],
                        'text' => "<i>".$data['data']."</i>",
                        'parse_mode' => 'HTML',
                    ]);
                    break;
            }
        }

        return 'Done';
    }





    public  function joinBotton($bot,$message)
    {
        $cacheKey = $message['chat']['id'].$bot->id.'_requiredJoinAction';
        if(Cache::has($cacheKey))
        {
            Cache::forget($cacheKey);
        }
        Cache::put($cacheKey,$bot->id, 40320);

        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
            <i> در این بخش میتوانید از طریق بات خود و کانالی که در اختیار دارید اقدام به عضوگیری نمایید</i>
            
            <i>ابتدا کانالی که قصد عضوگیری دارید را ساخته و ربات خود را به عنوان ادمین در کانال اضافه کنید و سپس نام کاربری کانال ساخته شده را در این بخش ارسال کنید</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }


    public function addRequiredJoin($bot,$message)
    {
        if(strpos($message['text'],'@') === 0)
        {
            $text = $message['text'];
        }
        else
        {
            $text = '@'.$message['text'];
        }

        $this->botton->createBotChannel($bot->id,$text);

        $cacheKey = $message['chat']['id'].$bot->id.'_requiredJoinAction';
        if(Cache::has($cacheKey))
        {
            Cache::forget($cacheKey);
        }

        $keyboard = [
            [trans('start.inactiveRequiredJoin')],
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
            <i> عضویت اجباری در کانال شما برای دسترسی به بات فعال شد</i>            
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }


    public function inactiveBotJoin($bot,$message)
    {

        $this->botton->deleteChannelBot($bot->id);
        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
            <i> عضوگیری کانال شما غیرفعال شد</i>            
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
