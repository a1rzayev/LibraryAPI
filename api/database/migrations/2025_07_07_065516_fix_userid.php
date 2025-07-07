<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add a new nullable uuid column to users
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // 2. Populate the uuid column
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update(['uuid' => Str::uuid()]);
        });

        // 3. Make uuid column not null
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });

        // 4. Add a new nullable user_uuid column to wishlist
        Schema::table('wishlist', function (Blueprint $table) {
            $table->uuid('user_uuid')->nullable()->after('user_id');
        });

        // 5. Copy user_id to user_uuid in wishlist
        DB::table('wishlist')->get()->each(function ($item) {
            $user = DB::table('users')->where('id', $item->user_id)->first();
            if ($user) {
                DB::table('wishlist')->where('id', $item->id)->update(['user_uuid' => $user->uuid]);
            }
        });

        // 6. Drop foreign key constraint on wishlist.user_id
        Schema::table('wishlist', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // 7. Drop primary key constraint on users.id
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary(['id']);
        });

        // 8. Drop old columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
        });
        Schema::table('wishlist', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        // 9. Rename new columns
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('uuid', 'id');
        });
        Schema::table('wishlist', function (Blueprint $table) {
            $table->renameColumn('user_uuid', 'user_id');
        });

        // 10. Set new id as primary key
        Schema::table('users', function (Blueprint $table) {
            $table->primary('id');
        });

        // 11. Add foreign key constraint back to wishlist
        Schema::table('wishlist', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not implemented for destructive migration
    }
}; 