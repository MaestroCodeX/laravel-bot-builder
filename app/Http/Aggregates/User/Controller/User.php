<?php   namespace App\Http\Aggregates\User\Controller;

use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Http\Aggregates\Bot\Contract\BotContract as Bot;
use App\Http\Aggregates\User\Contract\UserContract as User;

class UserController extends Controller
{


    private $user;
    private $bot;

    public function __construct(User $user, Bot $bot)
    {
        $this->user = $user;
        $this->bot = $bot;
    }

    public function register($telegram,$message)
    {
        $code = $this->get_by(5);
        $activation_code = Crypt::encrypt($code);
        $data = [
            'phone_number' => $message['contact']['phone_number'],
            'name' => $message['contact']['first_name'] ?? null,
            'last_name' => $message['contact']['last_name'] ?? null,
            'telegram_user_id' => $message['contact']['user_id'],
            'username' => $message['chat']['username'] ?? null,
            'activation_code' => $activation_code
        ];
        $this->user->register($data);

        $client = new Client();
        $mobile = str_replace(98,0,$message['contact']['phone_number']);
        $api = "https://api.kavenegar.com/v1/2B6D724555766A4848546E436854345477396D6F6E46724E427836694A6E4557/verify/lookup.json?receptor=".$mobile."&token=".$code."&template=Verify1";
        $client->request('GET',$api);

        $keyboard = [
            [trans('start.repeatSms')]
        ];
        
        $reply_markup = $telegram->replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
            <i>اطلاعات با موفقیت ذخیره شد</i>
            <i>پیامکی حاوی کد فعال سازی به شماره همراه شما ارسال شد لطفا آن را ارسال کنید</i>
        ";
        return $telegram->sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);

    }




    public function repeatSms($telegram,$message)
    {
        $code = $this->get_by(5);
        $activation_code = Crypt::encrypt($code);
        $data = [
            'activation_code' => $activation_code
        ];
        $this->user->update($message['chat']['id'],$data);

        $user = $this->user->get_user($message['chat']['id']);

        $client = new Client();
        $mobile = str_replace(98,0,$user->phone_number);
        $api = "https://api.kavenegar.com/v1/2B6D724555766A4848546E436854345477396D6F6E46724E427836694A6E4557/verify/lookup.json?receptor=".$mobile."&token=".$code."&template=Verify1";

        $client->request('GET',$api);

        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = $telegram->replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
            <i>پیامکی حاوی کد فعال سازی به شماره همراه شما ارسال شد لطفا آن را ارسال کنید</i>
        ";
        return $telegram->sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }
    


    public function checkAndActiveUser($telegram,$message)
    {
        $data = [
            'status' => 'ACTIVATE'
        ];
        $this->user->update($message['chat']['id'],$data);

        $keyboard = [
            [trans('start.createBotContinue')]
        ];

        $reply_markup = $telegram->replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
            <i>حساب کاربری شما فعال شد اکنون میتوانید بات های خود را بسازید</i>
        ";
        return $telegram->sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }


    private function get_by($length)
    {
        $chars ="1234567890";
        $final_rand="";
        for($i=0;$i<$length; $i++)
        {
            $final_rand .= $chars[ rand(0,strlen($chars)-1)];
        }
        return $final_rand;
    }




    public function userNotFound($telegram,$message)
    {
        $keyboard = [
            [trans('start.PreviusBtn')]
        ];

        $reply_markup = $telegram->replyKeyboardMarkup([
            'keyboard' => $keyboard, 
            'resize_keyboard' => true, 
            'one_time_keyboard' => false
        ]);
        $html = "
            <i>شما در بات عضو نشده اید</i>
        ";
        return $telegram->sendMessage([
            'chat_id' => $message['chat']['id'],
            'reply_to_message_id' => $message['message_id'], 
            'text' => $html, 
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);
    }

    
}