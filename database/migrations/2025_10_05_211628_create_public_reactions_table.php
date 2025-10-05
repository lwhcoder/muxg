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
            CREATE TABLE public.reactions (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                user_id UUID NOT NULL,
                message_id UUID NOT NULL,
                type TEXT NOT NULL,
                FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE,
                FOREIGN KEY (message_id) REFERENCES public.messages(id) ON DELETE CASCADE,
                UNIQUE(user_id, message_id, type)
            )
        ');

        DB::statement('CREATE INDEX idx_reactions_message_id ON public.reactions(message_id)');
        DB::statement('CREATE INDEX idx_reactions_user_id ON public.reactions(user_id)');
        DB::statement('CREATE INDEX idx_reactions_type ON public.reactions(type)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS public.reactions CASCADE');
    }
};
