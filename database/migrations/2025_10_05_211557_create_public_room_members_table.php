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
            CREATE TABLE public.room_members (
                room_id UUID NOT NULL,
                user_id UUID NOT NULL,
                joined_at TIMESTAMPTZ DEFAULT now(),
                PRIMARY KEY (room_id, user_id),
                FOREIGN KEY (room_id) REFERENCES public.rooms(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE
            )
        ');

        DB::statement('CREATE INDEX idx_room_members_room_id ON public.room_members(room_id)');
        DB::statement('CREATE INDEX idx_room_members_user_id ON public.room_members(user_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS public.room_members CASCADE');
    }
};
