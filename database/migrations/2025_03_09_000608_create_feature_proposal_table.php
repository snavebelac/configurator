<?php

use App\Models\Feature;
use App\Models\Proposal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feature_proposal', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Proposal::class);
            $table->foreignIdFor(Feature::class);
            $table->unsignedMediumInteger('quantity')->nullable();
            $table->integer('price')->nullable();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_proposal');
    }
};
