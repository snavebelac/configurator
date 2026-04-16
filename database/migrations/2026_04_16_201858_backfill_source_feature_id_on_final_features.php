<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill source_feature_id on any FinalFeature that predates the column
        // by matching on (tenant_id, name) against the feature library. Ambiguous
        // matches (multiple features with the same name) are skipped and left null.
        $rows = DB::table('final_features')
            ->whereNull('source_feature_id')
            ->select('id', 'tenant_id', 'name')
            ->get();

        foreach ($rows as $row) {
            $match = DB::table('features')
                ->where('tenant_id', $row->tenant_id)
                ->where('name', $row->name)
                ->whereNull('deleted_at')
                ->limit(2)
                ->pluck('id');

            if ($match->count() === 1) {
                DB::table('final_features')
                    ->where('id', $row->id)
                    ->update(['source_feature_id' => $match->first()]);
            }
        }
    }

    public function down(): void
    {
        // Non-reversible data migration.
    }
};
