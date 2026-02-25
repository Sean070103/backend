<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /** Guest user id used for unauthenticated votes (see StoreVoteRequest). */
    private const GUEST_USER_ID = 999999;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->insertOrIgnore([
            'id' => self::GUEST_USER_ID,
            'name' => 'Guest',
            'email' => 'guest@protocol.local',
            'password' => Hash::make('guest'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('id', self::GUEST_USER_ID)->delete();
    }
};
