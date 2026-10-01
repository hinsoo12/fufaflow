<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('number', 32);
            $table->string('name');
            $table->string('phone', 32)->nullable();
            $table->string('phone_e164', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('joined_on');
            $table->boolean('messages_opt_out')->default(false);
            $table->string('status', 32)->default('active');
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Lets child tables reference (tenant_id, id) so the database itself
            // rejects a row that points at another tenant's customer.
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'number']);
            $table->index(['tenant_id', 'phone_e164']);
            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
