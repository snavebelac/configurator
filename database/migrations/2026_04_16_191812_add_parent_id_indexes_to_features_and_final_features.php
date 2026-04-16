<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->index('parent_id');
        });

        Schema::table('final_features', function (Blueprint $table) {
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
        });

        Schema::table('final_features', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
        });
    }
};
