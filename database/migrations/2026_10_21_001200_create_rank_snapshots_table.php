<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rank_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('period_type');
            $table->date('period_start');
            $table->decimal('score', 12, 4);
            $table->unsignedInteger('rank');
            // The component metrics that produced the score, so a ranking can
            // be explained after the fact.
            $table->jsonb('metrics');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['business_id', 'period_type', 'period_start']);
            $table->index(['period_type', 'period_start', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_snapshots');
    }
};
