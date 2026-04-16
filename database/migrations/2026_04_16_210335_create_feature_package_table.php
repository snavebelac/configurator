<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_package', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('feature_id');
            $table->unsignedMediumInteger('quantity')->nullable();
            $table->boolean('optional')->nullable();
            $table->integer('price')->nullable();
            $table->timestamps();

            $table->unique(['package_id', 'feature_id']);
            $table->index('package_id');
            $table->index('feature_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_package');
    }
};
