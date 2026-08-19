<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposal_responses', function (Blueprint $table) {
            // Recorded alongside accepted_total rather than folded into it: a
            // build fee and a monthly fee are different commitments, and
            // summing them would misrepresent what the client agreed to.
            // Both in pence, per period.
            $table->integer('accepted_monthly')->default(0)->after('accepted_total');
            $table->integer('accepted_annually')->default(0)->after('accepted_monthly');
        });
    }

    public function down(): void
    {
        Schema::table('proposal_responses', function (Blueprint $table) {
            $table->dropColumn(['accepted_monthly', 'accepted_annually']);
        });
    }
};
