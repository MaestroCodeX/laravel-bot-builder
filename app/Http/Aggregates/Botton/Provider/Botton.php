<?php namespace App\Http\Aggregates\Botton\Provider;

use Illuminate\Support\ServiceProvider;
use App\Http\Aggregates\Botton\Contract\BottonContract;
use App\Http\Aggregate\Botton\Repository\BottonRepository;

class BottonServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }
   
    public function register()
    {
        $this->app->bind(BottonContract::class, BottonRepository::class);
    }
}
