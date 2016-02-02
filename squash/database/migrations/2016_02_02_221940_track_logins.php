<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrackLogins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('slack_logins', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('org_id');
          $table->integer('group_id');
          $table->integer('user_id');
          $table->integer('channel_id');
          $table->dateTime('created_at');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slack_logins');
    }
}
