<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Hashed, never stored or compared in plain text. Null means the
            // link is protected by the unguessable UUID alone.
            $table->string('access_password')->nullable()->after('status');

            // Null means the link never expires.
            $table->timestamp('access_expires_at')->nullable()->after('access_password');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['access_password', 'access_expires_at']);
        });
    }
};
