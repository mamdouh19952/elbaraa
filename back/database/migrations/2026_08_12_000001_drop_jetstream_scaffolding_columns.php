<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the schema left behind by the Jetstream/Fortify starter kit after it was
 * removed. This project is API-only (Sanctum Bearer tokens, Vue SPA) — nothing
 * reads two-factor, profile-photo, teams, or passkey data.
 *
 * The guards keep this safe on both paths: an existing database still has the
 * columns and drops them here, while a freshly migrated one may not (the passkeys
 * migration was deleted along with laravel/passkeys).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_values(array_filter([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'profile_photo_path',
                'current_team_id',
            ], fn (string $column) => Schema::hasColumn('users', $column)));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::dropIfExists('passkeys');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->after('password')->nullable();
            $table->text('two_factor_recovery_codes')->after('two_factor_secret')->nullable();
            $table->timestamp('two_factor_confirmed_at')->after('two_factor_recovery_codes')->nullable();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
        });
    }
};
