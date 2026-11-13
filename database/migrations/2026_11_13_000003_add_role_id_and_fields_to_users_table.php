<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained('roles')->nullOnDelete();
            $table->string('phone')->nullable()->after('role_id');
            $table->string('belt_level')->nullable()->after('phone');
            $table->foreignId('dojang_id')->nullable()->after('belt_level')->constrained('dojangs')->nullOnDelete();
            $table->decimal('rate_per_session', 12, 2)->default(50000.00)->after('dojang_id');
            $table->string('bank_name')->nullable()->after('rate_per_session');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_holder')->nullable()->after('bank_account_number');
            $table->string('avatar')->nullable()->after('bank_account_holder');
            $table->boolean('is_active')->default(true)->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['dojang_id']);
            $table->dropColumn([
                'role_id',
                'phone',
                'belt_level',
                'dojang_id',
                'rate_per_session',
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
                'avatar',
                'is_active',
            ]);
        });
    }
};

