<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_responses', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->unsignedBigInteger('tenant_id')->index();

            // One response per proposal. A client changing their mind means
            // the admin reopens the proposal, which clears the response.
            $table->unsignedBigInteger('proposal_id')->unique();

            $table->string('status');

            // The final_feature ids the client chose to keep. Required
            // features are not listed — they are implicit.
            $table->json('selected_feature_ids');

            // Recomputed server-side from the database at the moment of
            // acceptance, never taken from the client. Stored in pence so it
            // survives the source features being edited or removed later.
            $table->integer('accepted_total')->default(0);

            $table->text('note')->nullable();
            $table->timestamp('responded_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_responses');
    }
};
