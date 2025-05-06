<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->timestamp('poll_date');
            $table->integer('expected_number_court');
            $table->decimal('expected_price', 8, 2);
            $table->boolean('save_money_mode')->default(true);
            $table->integer('actual_number_court')->default(0);
            $table->decimal('actual_price', 8, 2)->default(0);
            $table->integer('number_member_registered')->default(0);
            $table->timestamp('closed_date')->nullable();
            $table->timestampsTz();
        });

        Schema::create('member_votes', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('player_uuid');
            $table->string('poll_uuid');
            $table->integer('number_go_with')->default(0);
            $table->timestamp('vote_date');
            $table->decimal('individual_price', 8, 2)->default(0);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('polls');
        Schema::dropIfExists('member_votes');
    }
};