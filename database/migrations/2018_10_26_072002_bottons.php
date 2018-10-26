<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Bottons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
 
        Schema::create('bottons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bot_id');
            $table->string('name',100);
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('bottons');
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
        Schema::drop("bottons");
    }
}
