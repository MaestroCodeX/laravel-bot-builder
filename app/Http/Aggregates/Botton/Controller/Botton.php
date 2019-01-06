<?php   namespace App\Http\Aggregates\Botton\Controller;

use File;
use GuzzleHttp\Client;
use Telegram;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Aggregates\User\Contract\UserContract as User;
use App\Http\Aggregates\Botton\Contract\BottonContract as Botton;
use Exception;

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
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $botton = json_decode($cacheGet);
        }
        $bottonId = (isset($botton) && !empty($botton)) ? $botton[0] : null;

        $this->botton->updateBottonType($bottonId,'article');

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

        switch ($botton->type)
        {
            case "article":
                return $this->articleData($bot,$botton,$message);
            case "faq":
                return $this->faqData($bot,$botton,$message);
            default:
                return "Done";
        }
    }


    private function articleData($bot,$botton,$message)
    {
        $bottonData = $this->botton->bottonData($bot->id,$botton->id);

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


    private function faqData($bot,$botton,$message)
    {
        $faqData = $this->botton->listOfFAQ($bot->id,$botton->id);
        if(isset($faqData) && !empty($faqData))
        {
            $question = $faqData->pluck('id');

            $userQuestionkey = $message['chat']['id'].$bot->id.'_userQuestionAnswer';
            if(Cache::has($userQuestionkey))
            {
                Cache::forget($userQuestionkey);
            }
            Cache::put($userQuestionkey,json_encode($question), 40320);

            $question = $this->botton->getQuestion($question[0]);

            $html = "
                <i>".$question->question."</i>
                ";

            Telegram::sendChatAction([
                'chat_id' => $message['chat']['id'],
                'action' => 'typing'
            ]);

            if($question->answer_type == "phone")
            {
                $keyboard = [
                    [
                        [
                        'text' => trans('start.sendPhoneNumberFaq'),
                        'request_contact' => true
                        ]
                    ],
                    [trans('start.PreviusBtn')]
                ];
            }
            else
            {
                $keyboard = [
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
    }



    public function userFAQanswer($bot,$message)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);

        $userQuestionkey = $message['chat']['id'].$bot->id.'_userQuestionAnswer';
        if(Cache::has($userQuestionkey))
        {
            $question = Cache::get($userQuestionkey);
            $arrayFaq = json_decode($question);
            $faqID = json_decode($question);
        }

        $getQuestion = $this->botton->getQuestion($faqID[0]);
        if($getQuestion->answer_type == "number" && !is_numeric($message["text"]))
        {
            return $this->mustBeValidFaqType($message,"number");
        }
        if($getQuestion->answer_type == "text" && !is_string($message["text"]))
        {
            return $this->mustBeValidFaqType($message,"text");
        }
        if($getQuestion->answer_type == "phone" && !isset($message['contact']) && !isset($message['contact']['phone_number']))
        {
            return $this->mustBeValidFaqType($message,"phone");
        }

        $user = $this->botton->get_user($message['chat']['id']);

//        $group = $this->botton->userAnswer($botton->id,$message['chat']['id']);

        $data = [
            "faq_id" => $faqID[0],
            "user_id" => $user->id,
            "answer" => (isset($message['contact']) && isset($message['contact']['phone_number'])) ? $message['contact']['phone_number'] : $message["text"],
            "group" => 1
        ];
        $this->botton->createAnswer($data);


        array_shift($arrayFaq);
        $arrayItems = (!is_array($arrayFaq)) ? (array)$arrayFaq : $arrayFaq;
        if(count($arrayItems) == 0)
        {
            return $this->responseDoneQuestion($bot,$message);
        }

        $userQuestionkey = $message['chat']['id'].$bot->id.'_userQuestionAnswer';
        if(Cache::has($userQuestionkey))
        {
            Cache::forget($userQuestionkey);
        }
        Cache::put($userQuestionkey,json_encode($arrayItems), 40320);


        $question = $this->botton->getQuestion($faqID[1]);

        $html = "
                <i>".$question->question."</i>
                ";

        if($question->answer_type == "phone")
        {
            $keyboard = [
                [[
                    'text' => trans('start.sendPhoneNumberFaq'),
                    'request_contact' => true
                ]],
                [trans('start.PreviusBtn')]
            ];
        }
        else
        {
            $keyboard = [
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



    private function responseDoneQuestion($bot,$message)
    {

        $userQuestionkey = $message['chat']['id'].$bot->id.'_userQuestionAnswer';
        if(Cache::has($userQuestionkey))
        {
            Cache::forget($userQuestionkey);
        }


        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $html = "
                <i>با موفقیت اطلاعات ثبت شد</i>
                <i>منتظر تایید مدیر باشید</i>
         ";

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


    private function mustBeValidFaqType($message,$type)
    {
        switch ($type)
        {
            case "number" :
                $text = " عددی ";
                break;
            case "text" :
                $text = " متنی ";
                break;
            case "phone" :
                $text = " شماره تماس ";
                break;
            default:
                $text = "";
        }
        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $html = "
                <code>متن ارسالی باید از نوع ".$text." باشد</code> 
         ";

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

        $channelBot = $this->botton->getChannelBot($bot->id);
        if(!is_null($channelBot))
        {
            $keyboard = [
                [trans('start.inactiveRequiredJoin')],
                [trans('start.PreviusBtn')]
            ];
        }
        else
        {
            $keyboard = [
                [trans('start.PreviusBtn')]
            ];
        }

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
            <i> نام کاربری کانال را ارسال کنید</i>
            <i>نکته : ابتدا ربات را در کانال مدیر کنید</i>
            
            <i>برای مثال : </i> <a href='@parsbehcombot'>@parsbehcombot</a> 
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

        try
        {
            $client = new Client();
            $api = "https://api.telegram.org/bot".$bot->token."/getChat?chat_id=".$text;
            $client->request('GET',$api)->getBody();

            $this->botton->createBotChannel($bot->id,$text);

            $cacheKey = $message['chat']['id'].$bot->id.'_requiredJoinAction';
            if(Cache::has($cacheKey))
            {
                Cache::forget($cacheKey);
            }

            $cacheKey = $message['chat']['id'].$bot->id.'_requiredJoinTextWarning';
            if(Cache::has($cacheKey))
            {
                Cache::forget($cacheKey);
            }
            Cache::put($cacheKey,$text, 40320);

            $keyboard = [
                [trans('start.PreviusBtn')]
            ];

            $reply_markup = Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false
            ]);

            $html = "
                <i> حالا متنی که هنگام عضو نبودن کاربر نمایش داده می شود را ارسال کنید</i>       
                
                <i>مثال:</i>     
                <i>لطفا در کانال</i> <a href='@parsbehcom'>@parsbehcom</a> <i>عضو شوید تا ربات برای شما فعال شود</i>
                
                <i>Parse Mode = HTML</i>
           ";

            return Telegram::sendMessage([
                'chat_id' => $message['chat']['id'],
                'reply_to_message_id' => $message['message_id'],
                'text' => $html,
                'parse_mode' => 'HTML',
                'reply_markup' => $reply_markup
            ]);
        }
        catch(Exception $e)
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
                <i>  اطلاعات کانال ارسال شده وجود ندارد</i>            
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





    public function addRequiredJoinwarningText($bot,$message)
    {
        $this->botton->updateBotChannelText($bot->id,$message['text']);

        $cacheKey = $message['chat']['id'].$bot->id.'_requiredJoinTextWarning';
        if(Cache::has($cacheKey))
        {
            Cache::forget($cacheKey);
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
                <i> با موفقیت ثبت شد</i>            
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
            <i> عضویت اجباری در کانال با موفقیت غیرفعال شد</i>            
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }




    public function createFaq($bot,$message)
    {
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $botton = json_decode($cacheGet);
        }
        $bottonId = (isset($botton) && !empty($botton)) ? $botton[0] : null;

        $faqs = $this->botton->listOfFAQ($bot->id,$bottonId);
        $countOfFAQ = (isset($faqs) && !empty($faqs)) ? count($faqs) : 0;

        if(!empty($faqs) && $countOfFAQ > 0)
        {
            $nameOfFaq = (isset($faqs[0]["name"]) && !empty($faqs[0]["name"])) ? $faqs[0]["name"] : "";
            $html = "
                <i>نام فرم سوال فعلی : ".$nameOfFaq."</i>
                <i>تعداد ".$countOfFAQ." سوال برای این دکمه ذخیره شده است برای ویرایش از دکمه مدیریت پاسخ فعلی استفاده کنید </i>
                <i>در غیر این صورت از 'اضافه کردن سوال' برای افزودن سوالات بیشتر و از 'ثبت سوالات جدید' برای پاک کردن سوالات ثبت شده استفاده کنید</i>
            ";

            $keyboard = [
                [trans('start.addAdditionalFaq')],
                [trans('start.addNewFaq')],
                [trans('start.PreviusBtn')]
            ];
        }
        else
        {
            $faqKey = $message['chat']['id'].$bot->id.'_createFaq';
            if(Cache::has($faqKey))
            {
                Cache::forget($faqKey);
            }
            Cache::put($faqKey,$bot->id, 40320);

            $html = "
                <i> توسط این قابلیت شما میتوانید فرمی از سوالات طراحی کنید که کاربر به آن ها پاسخ دهد و برای شما ارسال شود</i>     
                <i>پس از پاسخ به هر سوال , سوال بعدی برای کاربر ارسال می شود و پس از اتمام سوالات, لیست سوال و جواب ها برای شما ارسال می شود</i>  
                
                
                <i>خب متن اولین سوال را ارسال کنید</i>     
             ";

            $keyboard = [
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


    public function addFaq($bot,$message)
    {
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $botton = json_decode($cacheGet);
        }
        $bottonId = (isset($botton) && !empty($botton)) ? $botton[0] : null;

        $data = [
            "bot_id" => $bot->id,
            "botton_id" => $bottonId,
            "question" => $message["text"]
        ];
        $question_id = $this->botton->createFaq($data);

        $questionKey = $message['chat']['id'].$bot->id.'_answerType';
        if(Cache::has($questionKey))
        {
            Cache::forget($questionKey);
        }
        Cache::put($questionKey,$question_id->id, 40320);

        $keyboard = [
            [trans('start.numberFaq'), trans('start.textFaq')],
            [trans('start.phoneFaq')],
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
           <i>پاسخ دریافتی از کاربر برای این سوال از چه نوع باشد؟</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }


    public function addAnswerType($bot, $message, $type)
    {
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $botton = json_decode($cacheGet);
        }
        $bottonId = (isset($botton) && !empty($botton)) ? $botton[0] : null;


        $questionKey = $message['chat']['id'].$bot->id.'_answerType';
        $question = null;
        if(Cache::has($questionKey))
        {
           $question =  Cache::get($questionKey);
        }
        $this->botton->updateQuestion($bot->id,$question,$type,$bottonId);

        Cache::forget($questionKey);

        $keyboard = [
            [trans('start.finishFaq')],
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
                    <i>این سوال با موفقیت اضافه شد</i>
                    <i>سوال بعدی را ارسال کنید</i>
        
        
                    <i>برای پایان طرح سوال از دکمه 'اتمام' استفاده کنید</i>
                ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }


    public function finishFaq($bot, $message)
    {
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $botton = json_decode($cacheGet);
        }
        $bottonId = (isset($botton) && !empty($botton)) ? $botton[0] : null;

        $this->botton->updateBottonType($bottonId,'faq');

        $faqKey = $message['chat']['id'].$bot->id.'_createFaq';
        $questionKey = $message['chat']['id'].$bot->id.'_answerType';
        if(Cache::has($faqKey))
        {
            Cache::forget($faqKey);
        }
        if(Cache::has($questionKey))
        {
            Cache::forget($questionKey);
        }

        $faqs = $this->botton->listOfFAQ($bot->id,$bottonId);
        $countOfFAQ = (isset($faqs) && !empty($faqs)) ? count($faqs) : 0;
        $nameOfFaq = (isset($faqs[0]["name"]) && !empty($faqs[0]["name"])) ? $faqs[0]["name"] : "";
        if(!empty($faqs) && $countOfFAQ > 0 && !empty($nameOfFaq))
        {
            return $this->setFaqName($bot, $message);
        }

        $questionNameKey = $message['chat']['id'].$bot->id.'_nameForFaq';
        if(Cache::has($questionNameKey))
        {
            Cache::forget($questionNameKey);
        }
        Cache::put($questionNameKey,$bot->id, 40320);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
                    <i>خب یک نام برای این فرم سوال انتخاب کنید</i>
                ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }



    public function setFaqName($bot, $message)
    {
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        if(Cache::has($btnActionCacheKey))
        {
            $cacheGet = Cache::get($btnActionCacheKey);
            $botton = json_decode($cacheGet);
        }
        $bottonId = (isset($botton) && !empty($botton)) ? $botton[0] : null;


        $questionNameKey = $message['chat']['id'].$bot->id.'_nameForFaq';
        if(Cache::has($questionNameKey))
        {
            Cache::forget($questionNameKey);
            $this->botton->updateQuestionName($bot->id,$message["text"],$bottonId);
        }

        $faqs = $this->botton->listOfFAQ($bot->id,$bottonId);
        $countOfFAQ = (isset($faqs) && !empty($faqs)) ? count($faqs) : 0;
        if(!empty($faqs) && $countOfFAQ > 0)
        {
            $nameOfFaq = (isset($faqs[0]["name"]) && !empty($faqs[0]["name"])) ? $faqs[0]["name"] : "";
            $this->botton->updateQuestionName($bot->id,$nameOfFaq,$bottonId);
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
                    <i>سوالات با موفقیت ثبت شد</i>
                    
                    <i>برای مدیریت پاسخ سوالات از دکمه 'مدیریت پاسخ فعلی' استفاده کنید</i>
                ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }





     public function addAdditionalFaq($bot,$message)
     {
            $faqKey = $message['chat']['id'].$bot->id.'_createFaq';
            if(Cache::has($faqKey))
            {
                Cache::forget($faqKey);
            }
            Cache::put($faqKey,$bot->id, 40320);

            $html = "
                
                <i>خب متن سوال را ارسال کنید</i>     
             ";

            $keyboard = [
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





    public function addNewFaq($bot,$message)
    {
            $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
            if(Cache::has($btnActionCacheKey))
            {
                $cacheGet = Cache::get($btnActionCacheKey);
                $botton = json_decode($cacheGet);
            }
            $bottonId = (isset($botton) && !empty($botton)) ? $botton[0] : null;


            $this->botton->deleteAllFAQ($bot->id,$bottonId);

            $faqKey = $message['chat']['id'].$bot->id.'_createFaq';
            if(Cache::has($faqKey))
            {
                Cache::forget($faqKey);
            }
            Cache::put($faqKey,$bot->id, 40320);

            $html = "
                <i> توسط این قابلیت شما میتوانید فرمی از سوالات طراحی کنید که کاربر به آن ها پاسخ دهد و برای شما ارسال شود</i>     
                <i>پس از پاسخ به هر سوال , سوال بعدی برای کاربر ارسال می شود و پس از اتمام سوالات, لیست سوال و جواب ها برای شما ارسال می شود</i>  
                
                
                <i>خب متن اولین سوال را ارسال کنید</i>     
             ";

            $keyboard = [
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





}
