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
        DB::statement('
            CREATE TABLE auth.users (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                username VARCHAR(255) UNIQUE NOT NULL,
                hashed_password TEXT NOT NULL,
                avatar TEXT DEFAULT \'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg\',
                created_at TIMESTAMPTZ DEFAULT now()
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS auth.users CASCADE');
    }
};
