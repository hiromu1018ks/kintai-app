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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('職員ID');
            $table->date('attendance_date')->comment('打刻日');
            $table->timestamp('clock_in_time')->nullable()->comment('出勤時刻');
            $table->timestamp('clock_out_time')->nullable()->comment('退勤時刻');
            $table->text('clock_in_comment')->nullable()->comment('出勤時コメント');
            $table->text('clock_out_comment')->nullable()->comment('退勤時コメント');
            $table->foreignId('clock_in_modified_by')->nullable()->constrained('users')->comment('出勤修正者ID');
            $table->text('clock_in_modification_reason')->nullable()->comment('出勤修正理由');
            $table->foreignId('clock_out_modified_by')->nullable()->constrained('users')->comment('退勤修正者ID');
            $table->text('clock_out_modification_reason')->nullable()->comment('退勤修正理由');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
