<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('number', 32);
            $table->string('kind', 32)->default('standard');
            $table->string('status', 32)->default('issued');
            $table->date('issued_on');
            $table->date('due_on');
            $table->bigInteger('subtotal_minor');
            $table->bigInteger('discount_minor')->default(0);
            $table->bigInteger('tax_minor')->default(0);
            $table->bigInteger('total_minor');
            $table->bigInteger('amount_paid_minor')->default(0);
            $table->bigInteger('balance_minor');
            $table->char('currency', 3);
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'number']);
            $table->index(['tenant_id', 'status', 'due_on']);
            $table->index(['tenant_id', 'customer_id', 'status']);
            $table->index(['tenant_id', 'subscription_id']);
            $table->index(['tenant_id', 'issued_on']);

            $table->foreign(['tenant_id', 'customer_id'])
                ->references(['tenant_id', 'id'])->on('customers')->restrictOnDelete();
            $table->foreign(['tenant_id', 'subscription_id'])
                ->references(['tenant_id', 'id'])->on('subscriptions')->restrictOnDelete();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('invoice_id');
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->bigInteger('unit_price_minor');
            $table->bigInteger('amount_minor');
            $table->timestamps();

            $table->index(['tenant_id', 'invoice_id']);

            $table->foreign(['tenant_id', 'invoice_id'])
                ->references(['tenant_id', 'id'])->on('invoices')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
