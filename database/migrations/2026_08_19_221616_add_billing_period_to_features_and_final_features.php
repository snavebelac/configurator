<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['features', 'final_features'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                // Null for anything that isn't a recurring line. The price
                // column carries the amount per period, so no new money
                // column is needed.
                $blueprint->string('billing_period')->nullable()->after('percentage_rate');
            });
        }
    }

    public function down(): void
    {
        foreach (['features', 'final_features'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('billing_period');
            });
        }
    }
};
