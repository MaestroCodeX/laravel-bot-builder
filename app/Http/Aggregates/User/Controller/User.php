<?php   namespace App\Http\Aggregates\User\Controller;

use Telegram;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Aggregates\Bot\Contract\BotContract as Bot;
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

            if(isset($bot->deleted_at) && $bot->deleted_at !== null)
            {
                return $this->botIsRemoved($bot,$value['message']);
            }

            Telegram::sendChatAction([
                'chat_id' => $value['message']['chat']['id'],
                'action' => 'typing'
            ]);

            $btnActionCacheKey = $value['message']['chat']['id'].$bot->id.'_bottonAction';
            if(Cache::has($btnActionCacheKey))
            {
                $cacheGet = Cache::get($btnActionCacheKey);
                $btnInfo = json_decode($cacheGet);
            }
            $parentId = (isset($btnInfo) && !empty($btnInfo)) ? $btnInfo[0] : null;

            $alert = $value['message']['chat']['id'].$bot->id.'_botAlert';
            if(Cache::has($alert))
            {
                $alertGet = Cache::get($alert);
            }
            $action = (isset($alertGet) && !empty($alertGet)) ? $alertGet : null;

            if(isset($value['message']['text']))
            {

                $botton = $this->botton->existParentBtn($value['message']['text'],$bot->id,$value['message']['chat']['id'],$parentId);

                switch($value['message']['text'])
                {
                    case trans('start.StartBot'):
                        return $this->start($bot,$value['message']);
                    case trans('start.PreviusBtn'):
                        return $this->start($bot,$value['message']);
                    case trans('start.report'):
                        return $this->report($bot,$value['message']);
                    case trans('start.tools'):
                         return $this->tools($bot,$value['message']);
                    case trans('start.publicMessage'):
                        return $this->commingSoon($bot,$value['message']);
                        // return $this->publicMessage($value['message']);
                    case  trans('start.requiredJoin'):
                        return app(BottonController::class)->joinBotton($bot,$value['message']);
                    case trans('start.buttons'):
                        return app(BottonController::class)->buttons($bot,$value['message'],null);
                    case trans('start.inactiveRequiredJoin') :
                        return app(BottonController::class)->inactiveBotJoin($bot,$value['message']);
                    case strpos($value['message']['text'],trans('start.newBouttonKey')) === 0:
                        return app(BottonController::class)->newBottonName($bot,$value['message']);
                    case Cache::has($value['message']['chat']['id'].$bot->id.'_requiredJoinAction') :
                        return app(BottonController::class)->addRequiredJoin($bot,$value['message']);
                    case Cache::has($value['message']['chat']['id'].$bot->id.'_requiredJoinTextWarning') :
                        return app(BottonController::class)->addRequiredJoinwarningText($bot,$value['message']);
                    case Cache::has($value['message']['chat']['id'].$bot->id.'_bottonName') :
                        return app(BottonController::class)->insertNewParrentbotton($bot,$value['message']);
                    case trans('start.bottonSubMenu'):
                        return app(BottonController::class)->buttons($bot,$value['message'],Cache::get($value['message']['chat']['id'].$bot->id.'_bottonAction'));
                    case trans('start.editBottonName'):
                        return app(BottonController::class)->getEditBotton($bot,$value['message'],$botton);
                    case $action == 'editted':
                        return app(BottonController::class)->editBotton($bot,$value['message'],$botton);
                    case trans('start.bottonChangePosition'):
                        return app(BottonController::class)->getChangePosition($bot,$value['message'],$botton);
                    case $action == 'poistionChanged':
                        return app(BottonController::class)->changePosition($bot,$value['message'],$botton);
                    case trans('start.deleteBotton'):
                        return app(BottonController::class)->deleteBotton($bot,$value['message'],$botton);
                    case trans('start.bottonAnswer'):
                        return app(BottonController::class)->bottonAnswerBotton($bot,$value['message'],$botton);
                    case trans('start.showArticle'):
                        return app(BottonController::class)->showArticle‌Botton($bot,$value['message'],$botton);
                    case trans('start.doneCreateArticle'):
                        return app(BottonController::class)->doneCreateArticle($bot,$value['message']);
                    case trans('start.ascArticleSort'):
                        return app(BottonController::class)->ascArticleSort($bot,$value['message']);
                    case trans('start.addCaption'):
                        return app(BottonController::class)->captionMessage($bot,$value['message']);
                    case trans('start.descArticleSort'):
                        return app(BottonController::class)->descArticleSort($bot,$value['message']);
                    case Cache::has($value['message']['chat']['id'].$bot->id.'_bottonCaption'):
                        return app(BottonController::class)->getFileCaption($bot,$value['message']);
                    case Cache::has($value['message']['chat']['id'].$bot->id.'_bottonArticle'):
                        return app(BottonController::class)->getArticle‌Botton($bot,$value['message']);
                    case trans('start.createFaq'):
                        return app(BottonController::class)->createFaq($bot,$value['message']);
                    case trans('start.numberFaq'):
                        return app(BottonController::class)->addAnswerType($bot,$value['message'],"number");
                    case trans('start.finishFaq'):
                        return app(BottonController::class)->finishFaq($bot,$value['message']);
                    case trans('start.textFaq'):
                        return app(BottonController::class)->addAnswerType($bot,$value['message'],"text");
                    case trans('start.phoneFaq'):
                        return app(BottonController::class)->addAnswerType($bot,$value['message'],"phone");
                    case trans('start.addAdditionalFaq'):
                        return app(BottonController::class)->addAdditionalFaq($bot,$value['message']);
                    case trans('start.addNewFaq'):
                        return app(BottonController::class)->addNewFaq($bot,$value['message']);
                    case strpos($value['message']['text'],'question_') === 0:
                        return app(BottonController::class)->userAnswersForAdmin($bot,$value['message']);
                    case Cache::has($value['message']['chat']['id'].$bot->id.'_nameForFaq'):
                        return app(BottonController::class)->setFaqName($bot,$value['message']);
                    case Cache::has($value['message']['chat']['id'].$bot->id.'_createFaq'):
                        return app(BottonController::class)->addFaq($bot,$value['message']);
                    case !is_null($botton):
                        return app(BottonController::class)->bottonActions($bot,$value['message'],$botton);
                    default:
                        return $this->notFound($bot,$value['message']);
                }
            }

            if(Cache::has($value['message']['chat']['id'].$bot->id.'_bottonArticle'))
            {
                return app(BottonController::class)->getArticle‌Botton($bot,$value['message']);
            }
    }



    public function start($bot,$message)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $bcacheKey = $message['chat']['id'].$bot->id.'_bottonName';
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        $cacheKeys = $message['chat']['id'].$bot->id.'_botAlert';
        $bottonArticle = $message['chat']['id'].$bot->id.'_bottonArticle';
        $cacheKeyCaption = $message['chat']['id'].$bot->id.'_bottonCaption';
        $cacheKeyJoin = $message['chat']['id'].$bot->id.'_requiredJoinAction';
        $cacheKey = $message['chat']['id'].$bot->id.'_requiredJoinTextWarning';
        $faqKey = $message['chat']['id'].$bot->id.'_createFaq';
        $questionKey = $message['chat']['id'].$bot->id.'_answerType';
        $questionNameKey = $message['chat']['id'].$bot->id.'_nameForFaq';
        if(Cache::has($questionNameKey))
        {
            Cache::forget($questionNameKey);
        }
        if(Cache::has($questionKey))
        {
            Cache::forget($questionKey);
        }
        if(Cache::has($faqKey))
        {
            Cache::forget($faqKey);
        }
        if(Cache::has($bcacheKey))
        {
            Cache::forget($bcacheKey);
        }
        if(Cache::has($cacheKeyJoin))
        {
            Cache::forget($cacheKeyJoin);
        }
        if(Cache::has($cacheKeyCaption))
        {
            Cache::forget($cacheKeyCaption);
        }
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
        if(Cache::has($bottonArticle))
        {
            Cache::forget($bottonArticle);
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


    public function notFound($bot,$message)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $bcacheKey = $message['chat']['id'].$bot->id.'_bottonName';
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        $cacheKeys = $message['chat']['id'].$bot->id.'_botAlert';
        $bottonArticle = $message['chat']['id'].$bot->id.'_bottonArticle';
        $cacheKeyCaption = $message['chat']['id'].$bot->id.'_bottonCaption';
        $cacheKeyJoin = $message['chat']['id'].$bot->id.'_requiredJoinAction';
        $cacheKey = $message['chat']['id'].$bot->id.'_requiredJoinTextWarning';
        $faqKey = $message['chat']['id'].$bot->id.'_createFaq';
        $questionKey = $message['chat']['id'].$bot->id.'_answerType';
        $questionNameKey = $message['chat']['id'].$bot->id.'_nameForFaq';
        if(Cache::has($questionNameKey))
        {
            Cache::forget($questionNameKey);
        }
        if(Cache::has($questionKey))
        {
            Cache::forget($questionKey);
        }
        if(Cache::has($faqKey))
        {
            Cache::forget($faqKey);
        }
        if(Cache::has($bcacheKey))
        {
            Cache::forget($bcacheKey);
        }
        if(Cache::has($cacheKeyJoin))
        {
            Cache::forget($cacheKeyJoin);
        }
        if(Cache::has($cacheKeyCaption))
        {
            Cache::forget($cacheKeyCaption);
        }
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
        if(Cache::has($bottonArticle))
        {
            Cache::forget($bottonArticle);
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




    public function botIsRemoved($bot,$message)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $bcacheKey = $message['chat']['id'].$bot->id.'_bottonName';
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        $cacheKeys = $message['chat']['id'].$bot->id.'_botAlert';
        $bottonArticle = $message['chat']['id'].$bot->id.'_bottonArticle';
        $cacheKeyCaption = $message['chat']['id'].$bot->id.'_bottonCaption';
        $cacheKeyJoin = $message['chat']['id'].$bot->id.'_requiredJoinAction';
        $cacheKey = $message['chat']['id'].$bot->id.'_requiredJoinTextWarning';
        $faqKey = $message['chat']['id'].$bot->id.'_createFaq';
        $questionKey = $message['chat']['id'].$bot->id.'_answerType';
        $questionNameKey = $message['chat']['id'].$bot->id.'_nameForFaq';
        if(Cache::has($questionNameKey))
        {
            Cache::forget($questionNameKey);
        }
        if(Cache::has($questionKey))
        {
            Cache::forget($questionKey);
        }
        if(Cache::has($faqKey))
        {
            Cache::forget($faqKey);
        }
        if(Cache::has($bcacheKey))
        {
            Cache::forget($bcacheKey);
        }
        if(Cache::has($cacheKeyJoin))
        {
            Cache::forget($cacheKeyJoin);
        }
        if(Cache::has($cacheKeyCaption))
        {
            Cache::forget($cacheKeyCaption);
        }
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
        if(Cache::has($bottonArticle))
        {
            Cache::forget($bottonArticle);
        }

        $reply_markup = Telegram::replyKeyboardMarkup([
            'hide_keyboard' => true,
        ]);

        $html="
            <b>خطا</b>
            <code>ربات مورد نظر توسط مدیر حذف شده است</code>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }

    public function commingSoon($bot,$message)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $bcacheKey = $message['chat']['id'].$bot->id.'_bottonName';
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_bottonAction';
        $cacheKeys = $message['chat']['id'].$bot->id.'_botAlert';
        $bottonArticle = $message['chat']['id'].$bot->id.'_bottonArticle';
        $cacheKeyCaption = $message['chat']['id'].$bot->id.'_bottonCaption';
        $cacheKeyJoin = $message['chat']['id'].$bot->id.'_requiredJoinAction';
        $cacheKey = $message['chat']['id'].$bot->id.'_requiredJoinTextWarning';
        $faqKey = $message['chat']['id'].$bot->id.'_createFaq';
        $questionKey = $message['chat']['id'].$bot->id.'_answerType';
        $questionNameKey = $message['chat']['id'].$bot->id.'_nameForFaq';
        if(Cache::has($questionNameKey))
        {
            Cache::forget($questionNameKey);
        }
        if(Cache::has($questionKey))
        {
            Cache::forget($questionKey);
        }
        if(Cache::has($faqKey))
        {
            Cache::forget($faqKey);
        }
        if(Cache::has($bcacheKey))
        {
            Cache::forget($bcacheKey);
        }
        if(Cache::has($cacheKeyJoin))
        {
            Cache::forget($cacheKeyJoin);
        }
        if(Cache::has($cacheKeyCaption))
        {
            Cache::forget($cacheKeyCaption);
        }
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
        if(Cache::has($bottonArticle))
        {
            Cache::forget($bottonArticle);
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
            <code>این امکان به زودی در نسخه جدید اضافه خواهد شد</code>
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
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
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







    public function tools($bot,$message)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $keyboard = [
            [trans('start.requiredJoin')],
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
        <b>ابزارها</b>
        
        <b>عضویت اجباری در کانال</b>
        <i>با فعال کردن این ابزارک هر شخصی که به ربات شما پیام دهد باید در کانالی که شما مشخص کرده اید عضو باشد در غیر این صورت تا زمانی که در کانال شما عضو نشود ربات برایش فعال نمی شود</i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }







    //// user client bot///////
    /////////////////////////////

    public function UserBot($bot,$updates)
    {
            $value = $updates;

            if(isset($bot->deleted_at) && $bot->deleted_at !== null)
            {
                return $this->botIsRemoved($bot,$value['message']);
            }


            $channelBot = $this->botton->getChannelBot($bot->id);
            if(!is_null($channelBot))
            {
                $clientApi = new Client();
                $apiData = "https://api.telegram.org/bot".$bot->token."/getChatMember?chat_id=".$channelBot->username."&user_id=".$value['message']['chat']['id'];
                $responseData = $clientApi->get($apiData);
                $dataRes = (string)$responseData->getBody();
                $decode =  json_decode($dataRes,true);
                if(isset($decode['ok']) && $decode['ok'] == true && isset($decode['result']['status']) && $decode['result']['status'] !== "member")
                {
                    return $this->channelJoinWarning($bot,$value['message'],$channelBot);
                }
                $botUser = $this->botton->getBotUser($bot->id,$value['message']['chat']['id']);
                if(is_null($botUser))
                {
                    $data = [
                        "bot_id" => $bot->id,
                        "telegram_user_id" => (isset($decode['result']['user']['id'])) ? $decode['result']['user']['id'] : null,
                        "username" => (isset($decode['result']['user']['username'])) ? $decode['result']['user']['username'] : null,
                        "first_name" => (isset($decode['result']['user']['first_name'])) ? $decode['result']['user']['first_name'] : null,
                        "last_name" => (isset($decode['result']['user']['last_name'])) ? $decode['result']['user']['last_name'] : null,
                    ];
                    $this->botton->createBotUser($data);
                }
            }


            $btnActionCacheKey = $value['message']['chat']['id'].$bot->id.'_userBottonAction';
            if(Cache::has($btnActionCacheKey))
            {
                $cacheGet = Cache::get($btnActionCacheKey);
                $btnInfo = json_decode($cacheGet);
            }
            $parentId = (isset($btnInfo) && !empty($btnInfo)) ? $btnInfo[0] : null;


            if(isset($value['message']['contact']))
            {
                return app(BottonController::class)->userFAQanswer($bot,$value['message']);
            }

            if(isset($value['message']['text']))
            {
                $botton = $this->botton->existParentBtn($value['message']['text'],$bot->id,$value['message']['chat']['id'],$parentId);

                switch($value['message']['text'])
                {
                    case trans('start.StartBot'):
                        return $this->UserStart($value['message'],$bot);
                    case trans('start.PreviusBtn'):
                        return $this->UserStart($value['message'],$bot);
                    case Cache::has($value['message']['chat']['id'].$bot->id.'_userQuestionAnswer'):
                        return app(BottonController::class)->userFAQanswer($bot,$value['message']);
                    case isset($botton) && !empty($botton):
                        return app(BottonController::class)->UerBottonActions($bot,$value['message'],$botton);
                    default:
                        return $this->userNotFound($value['message'],$bot);
                }
            }
    }





    public function channelJoinWarning($botID,$message,$channelBot)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);

        $inline_keyboard = json_encode([
            'inline_keyboard'=>[
                [
                    ['text'=>$channelBot->username,'url'=>'https://t.me/'.str_replace("@","",$channelBot->username)]
                ],
            ]
        ]);

        $html="
            <code>
                ".$channelBot->message."
            </code>
            
            <i>بعد از عضویت در کانال, ربات را مجددا </i> <a href='/start'>start</a> <i> کنید </i>
        ";

        return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $inline_keyboard
        ]);
    }


    public function userStart($message,$bot)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_userBottonAction';
        $userQuestionkey = $message['chat']['id'].$bot->id.'_userQuestionAnswer';
        if(Cache::has($userQuestionkey))
        {
            Cache::forget($userQuestionkey);
        }
        if(Cache::has($btnActionCacheKey))
        {
            Cache::forget($btnActionCacheKey);
        }
        $bottons = $this->botton->bottonList($bot,NULL);
        $groupBottons = $bottons->groupBy('position');
        $encodeBtn = json_encode($groupBottons);
        $decodeBtn = json_decode($encodeBtn,true);
        $keyboards = [];
        foreach($decodeBtn as $key => $gb)
        {
            $btn = array_column($gb,'name');
            $keyboards[] = $btn;
        }

        $keyboard = $keyboards;


        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $html = "
            <i>سلام</i>,
            <i>خوش آمدید</i>
        ";

         return Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'],
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);

    }


    public function userNotFound($message,$bot)
    {
        Telegram::sendChatAction([
            'chat_id' => $message['chat']['id'],
            'action' => 'typing'
        ]);
        $btnActionCacheKey = $message['chat']['id'].$bot->id.'_userBottonAction';
        $userQuestionkey = $message['chat']['id'].$bot->id.'_userQuestionAnswer';
        if(Cache::has($userQuestionkey))
        {
            Cache::forget($userQuestionkey);
        }
        if(Cache::has($btnActionCacheKey))
        {
            Cache::forget($btnActionCacheKey);
        }
        $bottons = $this->botton->bottonList($bot,NULL);
        $groupBottons = $bottons->groupBy('position');
        $encodeBtn = json_encode($groupBottons);
        $decodeBtn = json_decode($encodeBtn,true);
        $keyboards = [];
        foreach($decodeBtn as $key => $gb)
        {
            $btn = array_column($gb,'name');
            $keyboards[] = $btn;
        }

        $keyboard = $keyboards;

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
