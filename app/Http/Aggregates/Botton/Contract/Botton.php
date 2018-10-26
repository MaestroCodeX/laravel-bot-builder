<?php namespace App\Http\Aggregates\Botton\Contract;

interface  BottonContract
{
    public function createBotton($data);

    public function parentBottonList($bot);

}