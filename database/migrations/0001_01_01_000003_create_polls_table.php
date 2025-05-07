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
            $table->integer('total_court');
            $table->integer('total_hours');
            $table->decimal('total_price', 8, 2);
            $table->timestamp('closed_date')->nullable();
            $table->timestampsTz();
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('player_uuid');
            $table->string('poll_uuid');
            $table->integer('slot')->default(0);
            $table->timestamp('voted_date');
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
        Schema::dropIfExists('votes');
    }
};