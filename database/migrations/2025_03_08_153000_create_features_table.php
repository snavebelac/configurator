<?php

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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name');
            $table->text('description');
            $table->integer('price');
            $table->unsignedMediumInteger('quantity');
            $table->boolean('optional')->default(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedMediumInteger('order')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
