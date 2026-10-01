<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('business_type', 32)->nullable();
            $table->char('country', 2);
            $table->char('currency', 3);
            $table->string('timezone', 64);
            $table->string('locale', 10)->default('en');
            $table->string('status', 32)->default('active');
            $table->date('trial_ends_on')->nullable();
            $table->date('paid_until')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_login_at');
        });

        Schema::dropIfExists('tenants');
    }
};
