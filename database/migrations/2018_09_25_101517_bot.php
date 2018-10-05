<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Bot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
                Schema::disableForeignKeyConstraints();

                    Schema::create('bots', function (Blueprint $table) {
                        $table->increments('id');
                        $table->unsignedInteger('user_id')->nullable();
                        $table->unsignedBigInteger('bot_id')->nullable();
                        $table->string('name',100)->nullable();
                        $table->string('username',100)->index();
                        $table->string('token',700)->index();
                        $table->text('description')->nullable();
                        $table->foreign('user_id')->references('id')->on('users');
                        $table->timestamps();
                        $table->softDeletes();

                    });

                Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("bots");
    }
}
