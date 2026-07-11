<?php

use App\Enums\OccupancyStatus;
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
        Schema::create('occupancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained('users');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('status')->default(OccupancyStatus::ACTIVE->value);
            $table->timestamps();

            $table->index(['organization_id', 'resident_id']);
            $table->index(['unit_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupancies');
    }
};
