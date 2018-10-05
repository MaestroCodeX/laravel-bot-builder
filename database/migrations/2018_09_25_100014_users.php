<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone_number',20)->index();
            $table->integer('telegram_user_id')->nullabel();
            $table->string('name',100)->nullable();
            $table->enum('status',['ACTIVATE','DEACTIVATE'])->default('DEACTIVATE');
            $table->string('last_name',100)->nullable();
            $table->string('username',100)->nullable()->index();
            $table->text('vcard')->nullable();
            $table->string('activation_code',300)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("users");
    }
}
