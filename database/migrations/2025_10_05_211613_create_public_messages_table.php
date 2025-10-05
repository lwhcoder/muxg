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
            CREATE TABLE public.messages (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID NOT NULL,
                room_id UUID NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMPTZ DEFAULT now(),
                FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE,
                FOREIGN KEY (room_id) REFERENCES public.rooms(id) ON DELETE CASCADE
            )
        ');

        DB::statement('CREATE INDEX idx_messages_room_id ON public.messages(room_id)');
        DB::statement('CREATE INDEX idx_messages_user_id ON public.messages(user_id)');
        DB::statement('CREATE INDEX idx_messages_created_at ON public.messages(created_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS public.messages CASCADE');
    }
};
