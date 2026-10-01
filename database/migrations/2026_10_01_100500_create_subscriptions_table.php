<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('plan_id');

            // Snapshot of the plan at start/renewal, so plan edits never rewrite
            // an existing customer's terms.
            $table->bigInteger('price_minor');
            $table->boolean('price_overridden')->default(false);
            $table->char('currency', 3);
            $table->string('interval_unit', 16);
            $table->unsignedSmallInteger('interval_count');
            $table->string('renewal_mode', 16);
            $table->unsignedSmallInteger('term_periods')->nullable();

            $table->unsignedTinyInteger('anchor_day');
            $table->date('starts_on');
            $table->date('current_period_ends_on');
            $table->unsignedInteger('periods_count')->default(0);

            // Stored decisions only; time-based phases are derived.
            $table->string('status', 32)->default('active');
            $table->date('cancel_on')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'status', 'current_period_ends_on']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'plan_id', 'status']);

            $table->foreign(['tenant_id', 'customer_id'])
                ->references(['tenant_id', 'id'])->on('customers')->restrictOnDelete();
            $table->foreign(['tenant_id', 'plan_id'])
                ->references(['tenant_id', 'id'])->on('plans')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
