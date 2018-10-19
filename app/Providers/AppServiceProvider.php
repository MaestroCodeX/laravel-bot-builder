<?php namespace App\Providers;

use Telegram\Bot\Api;
use Illuminate\Support\ServiceProvider;
use App\Http\Aggregates\AdminBot\Controller\AdminBotController;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // $telegram = new Api(config('telegram.bot_token'));

        // $telegram->removeWebhook();

        // $telegram->setWebhook(['url' => config('telegram.webhook_url').config('telegram.bot_token').'/webhook']);


        //  $users = app(BotController::class)->botList();

        //  foreach($users as $user)
        //  {
        //     app(UserController::class)->UserBot($user->token);
        //  }

        // app(AdminBotController::class)->AdminBot();

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
