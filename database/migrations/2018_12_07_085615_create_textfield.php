<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTextfield extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bot_channel', function (Blueprint $table) {
            $table->string('message',2000)->default("برای دسترسی به ربات ابتدا باید در کانال ربات عضو شوید");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bot_channel', function (Blueprint $table) {
            $table->dropColumn("message");
        });
    }
}
