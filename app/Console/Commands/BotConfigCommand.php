<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class BotConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'initialize bot config';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
         $telegram = new Api(config('telegram.bot_token'));
         $telegram->removeWebhook();
         sleep(5);
         $telegram->setWebhook(['url' => config('telegram.webhook_url').config('telegram.bot_id').'/webhook']);
    }
}
