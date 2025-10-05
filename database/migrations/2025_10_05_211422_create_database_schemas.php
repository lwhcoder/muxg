<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create auth schema if it doesn't exist
        DB::statement('CREATE SCHEMA IF NOT EXISTS auth');
        
        // Ensure UUID extension is available
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        
        // Enable gen_random_uuid() function
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop auth schema
        DB::statement('DROP SCHEMA IF EXISTS auth CASCADE');
    }
};
