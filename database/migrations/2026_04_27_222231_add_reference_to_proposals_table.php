<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('reference', 16)->nullable()->after('name');
        });

        // Backfill existing rows: assign references in (tenant_id, year of created_at,
        // created_at) order, with the per-year sequence resetting per tenant.
        DB::table('proposals')
            ->orderBy('tenant_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'tenant_id', 'created_at'])
            ->groupBy(fn ($p) => $p->tenant_id.':'.substr((string) $p->created_at, 0, 4))
            ->each(function ($group) {
                $i = 0;
                foreach ($group as $proposal) {
                    $i++;
                    $year = substr((string) $proposal->created_at, 0, 4);
                    $reference = $year.'/'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
                    DB::table('proposals')->where('id', $proposal->id)->update(['reference' => $reference]);
                }
            });

        Schema::table('proposals', function (Blueprint $table) {
            $table->unique(['tenant_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'reference']);
            $table->dropColumn('reference');
        });
    }
};
