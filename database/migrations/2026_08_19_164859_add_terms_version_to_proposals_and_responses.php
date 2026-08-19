<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Pinned when the proposal is marked delivered, so editing a
            // tenant's terms afterwards can't rewrite what was sent.
            $table->unsignedBigInteger('terms_version_id')->nullable()->index()->after('access_expires_at');
        });

        Schema::table('proposal_responses', function (Blueprint $table) {
            // Recorded again on the response, separately from the proposal.
            // A proposal can be reopened and re-sent against newer terms; the
            // answer the client actually gave must keep pointing at the terms
            // that were in force when they gave it.
            $table->unsignedBigInteger('terms_version_id')->nullable()->index()->after('accepted_total');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('terms_version_id');
        });

        Schema::table('proposal_responses', function (Blueprint $table) {
            $table->dropColumn('terms_version_id');
        });
    }
};
