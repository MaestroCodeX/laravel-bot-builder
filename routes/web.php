<?php


/*
|--------------------------------------------------------------------------
| Amirali Roshanaei - mr.roshanae@gmail.com
|--------------------------------------------------------------------------
|
*/

Route::post('/{botID}/webhook', '\App\Http\Aggregates\Start\Controller\StartController@init');



