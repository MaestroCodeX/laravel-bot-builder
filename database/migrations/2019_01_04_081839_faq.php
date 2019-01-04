<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Faq extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('bot_faq', function (Blueprint $table) {
            $table->increments('id');
            $table->string('question',1000);
            $table->string('answer_type',100);
            $table->string('name',100)->nullable();
            $table->unsignedInteger('bot_id');
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
        Schema::dropIfExists('bot_faq');
    }
}
