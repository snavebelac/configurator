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
        Schema::create('final_features', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('proposal_id')->index();
            $table->string('name');
            $table->text('description');
            $table->integer('price');
            $table->unsignedMediumInteger('quantity');
            $table->boolean('optional')->default(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedMediumInteger('order')->default(1);
            $table->string('notes', 4000)->nullable();
            $table->string('client_notes', 4000)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_features');
    }
};
