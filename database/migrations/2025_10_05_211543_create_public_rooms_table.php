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
            CREATE TABLE public.rooms (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                name VARCHAR(255) NOT NULL,
                description TEXT DEFAULT \'\',
                created_at TIMESTAMPTZ DEFAULT now(),
                visibility VARCHAR(50) NOT NULL CHECK (visibility IN (\'public\', \'private\'))
            )
        ');

        DB::statement('CREATE INDEX idx_rooms_visibility ON public.rooms(visibility)');
        DB::statement('CREATE INDEX idx_rooms_created_at ON public.rooms(created_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS public.rooms CASCADE');
    }
};
