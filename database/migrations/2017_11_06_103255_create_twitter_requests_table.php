<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitterRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('request_ident')->unique();
            // NOTE: Limit is 16 chars, but might as well make it 50 in DB
            $table->string('twitter_username', 50);
            $table->string('access_token');
            $table->timestamps();

            $table->index('twitter_username');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twitter_requests');
    }
}
