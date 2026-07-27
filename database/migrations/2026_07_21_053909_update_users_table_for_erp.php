<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('status', 20)->default('active')->after('avatar');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete()->after('status');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete()->after('company_id');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('last_login_ip');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->softDeletes();

            $table->index('status');
            $table->index('company_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'phone', 'avatar', 'status', 'company_id', 'branch_id',
                'last_login_at', 'last_login_ip', 'created_by', 'updated_by',
            ]);
        });
    }
};
