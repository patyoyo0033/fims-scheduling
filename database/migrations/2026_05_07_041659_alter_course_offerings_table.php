<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_offerings', function (Blueprint $table) {
            $table->enum('approval_status', ['draft', 'pending', 'published', 'rejected'])->default('draft')->after('coordinator_id');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->softDeletes();
        });

        Schema::table('course_offerings', function (Blueprint $table) {
            $table->dropColumn(['is_practicum', 'settings']);
        });
    }

    public function down(): void
    {
        Schema::table('course_offerings', function (Blueprint $table) {
            $table->boolean('is_practicum')->default(false);
            $table->json('settings')->nullable();
        });

        Schema::table('course_offerings', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'rejection_reason', 'deleted_at']);
        });
    }
};
