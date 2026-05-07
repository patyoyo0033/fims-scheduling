<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Because location_type was an ENUM, SQLite cannot drop it. We'll add the new columns.
            // If using MySQL, we can drop the column. Let's assume MySQL for now.
            // But to be safe, if we have existing data, dropping it loses data.
            // Let's add location_type_id first.
            $table->foreignId('location_type_id')->nullable()->after('capacity')->constrained('location_types')->nullOnDelete();
            $table->json('equipment_type')->nullable()->after('location_type_id');
            $table->softDeletes();
        });

        // Optional: We can drop the old enum column if we want, but it's risky if we don't migrate data.
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('location_type');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->enum('location_type', ['lecture', 'lab', 'ward'])->default('lecture')->after('capacity');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['location_type_id']);
            $table->dropColumn(['location_type_id', 'equipment_type', 'deleted_at']);
        });
    }
};
