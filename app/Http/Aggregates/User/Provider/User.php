<?php namespace App\Http\Aggregates\User\Provider;

use Illuminate\Support\ServiceProvider;
use App\Http\Aggregates\User\Contract\UserContract;
use App\Http\Aggregate\User\Repository\UserRepository;

class UserServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }
   
    public function register()
    {
        $this->app->bind(UserContract::class, UserRepository::class);
    }
}
