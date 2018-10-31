<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BottonData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
 
        Schema::create('botton_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type',100)->nullable();
            $table->string('fileID',200)->nullable();
            $table->string('fileSize',20)->nullable();
            $table->string('sort',25)->defualt('ASC');
            $table->text('data');
            $table->unsignedInteger('bot_id');
            $table->unsignedInteger('botton_id');
            $table->foreign('botton_id')->references('id')->on('bottons');
            $table->foreign('bot_id')->references('id')->on('bots');
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
        Schema::drop("botton_data");
    }
}
