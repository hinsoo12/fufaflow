<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per covered period: the persisted "billing cycle".
        Schema::create('subscription_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedInteger('sequence');
            $table->date('starts_on');
            $table->date('ends_on'); // inclusive
            // Terms in force for this period, so a voided renewal can be reversed exactly.
            $table->unsignedBigInteger('plan_id');
            $table->unsignedTinyInteger('anchor_day');
            $table->bigInteger('price_minor');
            $table->char('currency', 3);
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('source', 32);
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            // Makes a double-submitted renewal fail safely.
            $table->unique(['subscription_id', 'starts_on']);
            $table->unique(['subscription_id', 'sequence']);
            $table->index(['tenant_id', 'ends_on']);
            $table->index(['tenant_id', 'invoice_id']);

            $table->foreign(['tenant_id', 'subscription_id'])
                ->references(['tenant_id', 'id'])->on('subscriptions')->restrictOnDelete();
            $table->foreign(['tenant_id', 'invoice_id'])
                ->references(['tenant_id', 'id'])->on('invoices')->restrictOnDelete();
            $table->foreign(['tenant_id', 'plan_id'])
                ->references(['tenant_id', 'id'])->on('plans')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_periods');
    }
};
