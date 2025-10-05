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
            CREATE TABLE auth.sessions (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID NOT NULL,
                token TEXT NOT NULL,
                created_at TIMESTAMPTZ DEFAULT now(),
                expires_at TIMESTAMPTZ NOT NULL,
                device_info TEXT,
                FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE
            )
        ');

        DB::statement('CREATE INDEX idx_sessions_token ON auth.sessions(token)');
        DB::statement('CREATE INDEX idx_sessions_user_id ON auth.sessions(user_id)');
        DB::statement('CREATE INDEX idx_sessions_expires_at ON auth.sessions(expires_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS auth.sessions CASCADE');
    }
};
