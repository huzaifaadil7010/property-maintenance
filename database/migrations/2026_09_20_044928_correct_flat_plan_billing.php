<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->renameColumn('unit_amount', 'amount');
        });

        Schema::table('plans', function (Blueprint $table): void {
            $table->unsignedSmallInteger('unit_creation_limit')->default(250)->after('amount');
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->timestamp('current_period_starts_at')->nullable()->after('trial_ends_at');
            $table->timestamp('current_period_ends_at')->nullable()->after('current_period_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropColumn(['current_period_starts_at', 'current_period_ends_at']);
        });

        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn('unit_creation_limit');
        });

        Schema::table('plans', function (Blueprint $table): void {
            $table->renameColumn('amount', 'unit_amount');
        });
    }
};
