<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->whereRaw("public_token NOT REGEXP '^[0-9a-f]{32}$'")
            ->orderBy('id')
            ->chunkById(200, function ($orders) {
                foreach ($orders as $order) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['public_token' => bin2hex(random_bytes(16))]);
                }
            });
    }

    public function down(): void
    {
        // Intentionally empty — token values cannot be meaningfully reversed.
    }
};
