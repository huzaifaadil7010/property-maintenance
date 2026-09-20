<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_unit_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->timestamp('period_starts_at');
            $table->timestamp('period_ends_at');
            $table->unsignedSmallInteger('units_created')->default(0);
            $table->timestamps();

            $table->unique(['subscription_id', 'period_starts_at']);
            $table->index(['organization_id', 'period_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_unit_usages');
    }
};
