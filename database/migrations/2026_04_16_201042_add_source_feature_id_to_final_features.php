<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('final_features', function (Blueprint $table) {
            $table->unsignedBigInteger('source_feature_id')->nullable()->after('parent_id');
            $table->index('source_feature_id');
        });
    }

    public function down(): void
    {
        Schema::table('final_features', function (Blueprint $table) {
            $table->dropIndex(['source_feature_id']);
            $table->dropColumn('source_feature_id');
        });
    }
};
