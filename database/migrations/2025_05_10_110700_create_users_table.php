<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique()->comment('職員番号'); // 追加
            $table->string('name');
            $table->string('email')->unique(); // Breezeが生成済み
            $table->timestamp('email_verified_at')->nullable(); // Breezeが生成済み
            $table->string('password'); // Breezeが生成済み

            $table->foreignId('department_id')->nullable()->constrained('departments')->comment('所属ID'); // 追加 (NULL許容は適宜判断)
            $table->string('job_title')->nullable()->comment('役職'); // 追加
            $table->foreignId('role_id')->constrained('roles')->comment('権限ID'); // 追加
            $table->foreignId('work_pattern_id')->nullable()->constrained('work_patterns')->comment('勤務形態ID'); // 追加 (NULL許容は適宜判断)
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->comment('上司ID'); // 追加 (自己参照)

            $table->rememberToken(); // Breezeが生成済み
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
