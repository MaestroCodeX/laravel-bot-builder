<?php namespace App\Providers;

use Telegram\Bot\Api;
use Illuminate\Support\ServiceProvider;

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
        // sleep(5);
        // $telegram->setWebhook(['url' => config('telegram.webhook_url').config('telegram.bot_id').'/webhook']);
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
