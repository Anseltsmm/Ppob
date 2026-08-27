<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Shared-secret untuk memvalidasi callback OkeConnect (GET tanpa signature).
        // Dipakai sebagai query param ?token=... pada callback URL di dashboard OkeConnect.
        if (DB::table('settings')->where('key', 'okeconnect_callback_token')->doesntExist()) {
            DB::table('settings')->insert([
                'key' => 'okeconnect_callback_token',
                'value' => Str::random(32),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'okeconnect_callback_token')->delete();
    }
};
