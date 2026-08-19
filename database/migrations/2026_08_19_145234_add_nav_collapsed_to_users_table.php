<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Defaults to false so a new user sees labels next to the icons —
            // collapsing to icons only is an informed choice, not a first-run
            // guessing game.
            $table->boolean('nav_collapsed')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nav_collapsed');
        });
    }
};
