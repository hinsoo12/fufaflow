<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('customer_id');
            $table->string('receipt_number', 32);
            $table->bigInteger('amount_minor');
            $table->char('currency', 3);
            $table->date('received_on');
            $table->unsignedBigInteger('payment_method_id');
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 32)->default('recorded');
            $table->string('idempotency_key', 64);
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'receipt_number']);
            $table->unique(['tenant_id', 'idempotency_key']);
            $table->index(['tenant_id', 'received_on']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'recorded_by', 'received_on']);
            $table->index(['tenant_id', 'reference']);

            $table->foreign(['tenant_id', 'customer_id'])
                ->references(['tenant_id', 'id'])->on('customers')->restrictOnDelete();
            $table->foreign(['tenant_id', 'payment_method_id'])
                ->references(['tenant_id', 'id'])->on('payment_methods')->restrictOnDelete();
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('invoice_id');
            $table->bigInteger('amount_minor');
            $table->timestamp('voided_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['payment_id', 'invoice_id']);
            $table->index(['tenant_id', 'invoice_id']);

            $table->foreign(['tenant_id', 'payment_id'])
                ->references(['tenant_id', 'id'])->on('payments')->restrictOnDelete();
            $table->foreign(['tenant_id', 'invoice_id'])
                ->references(['tenant_id', 'id'])->on('invoices')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
