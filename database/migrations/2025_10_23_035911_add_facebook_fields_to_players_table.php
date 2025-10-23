<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('facebook_id')->nullable()->unique()->after('email');
            $table->json('facebook_profile')->nullable()->after('facebook_id');
            $table->timestamp('facebook_connected_at')->nullable()->after('facebook_profile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['facebook_id', 'facebook_profile', 'facebook_connected_at']);
        });
    }
};
