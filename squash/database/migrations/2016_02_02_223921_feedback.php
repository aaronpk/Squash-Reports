<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Feedback extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('feedback', function (Blueprint $table) {
          $table->increments('id');
          $table->dateTime('created_at');
          $table->integer('user_id');
          $table->integer('org_id');
          $table->integer('group_id');
          $table->integer('channel_id');
          $table->text('text');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedback');
    }
}
