<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->foreignId('plan_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->after('plan_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('payment_method_id');
            $table->dropConstrainedForeignId('plan_id');
        });
    }
};
