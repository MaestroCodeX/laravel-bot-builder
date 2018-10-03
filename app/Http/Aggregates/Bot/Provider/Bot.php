<?php namespace App\Http\Aggregates\Bot\Provider;

use Illuminate\Support\ServiceProvider;
use App\Http\Aggregates\Bot\Contract\BotContract;
use App\Http\Aggregate\Bot\Repository\BotRepository;

class BotServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }
   
    public function register()
    {
        $this->app->bind(BotContract::class, BotRepository::class);
    }
}
