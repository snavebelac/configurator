<?php

use App\Enums\PricingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['features', 'final_features'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('pricing_type')->default(PricingType::Fixed->value)->after('price');

                // Basis points, so 12.5% is 1250 and the value stays an
                // integer — the same reason prices are held in pence.
                // Only meaningful when pricing_type is 'percentage'.
                $blueprint->unsignedInteger('percentage_rate')->default(0)->after('pricing_type');
            });
        }
    }

    public function down(): void
    {
        foreach (['features', 'final_features'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['pricing_type', 'percentage_rate']);
            });
        }
    }
};
