<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name');

            // Exactly one set per tenant should carry this; enforced in the
            // model rather than the schema, since MySQL can't express a
            // partial unique index.
            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });

        Schema::create('terms_versions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('terms_id')->index();

            // Per-set, 1-based. Draft rows carry the number they *would* take
            // on publish, so the UI can show it before it's committed.
            $table->unsignedInteger('version');

            // Sanitised HTML. Never rendered without having gone through
            // App\Helpers\HtmlSanitiser on the way in.
            $table->longText('body')->nullable();

            // Null means draft. Only published versions may be pinned to a
            // proposal, so a half-written draft can never reach a client.
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique(['terms_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms_versions');
        Schema::dropIfExists('terms');
    }
};
