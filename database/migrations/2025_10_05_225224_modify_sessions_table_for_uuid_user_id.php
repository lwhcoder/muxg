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
        Schema::table('sessions', function (Blueprint $table) {
            // Drop the existing user_id column and recreate it as UUID
            $table->dropColumn('user_id');
        });
        
        Schema::table('sessions', function (Blueprint $table) {
            // Add the new UUID user_id column
            $table->uuid('user_id')->nullable()->index()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Drop the UUID user_id column
            $table->dropColumn('user_id');
        });
        
        Schema::table('sessions', function (Blueprint $table) {
            // Restore the original bigint user_id column
            $table->foreignId('user_id')->nullable()->index()->after('id');
        });
    }
};
