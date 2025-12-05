<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change format enum to string to support more formats
        Schema::table('deck_imports', function (Blueprint $table) {
            // Add new columns
            $table->string('original_filename')->nullable()->after('filename');
            $table->string('file_path')->nullable()->after('original_filename');
            $table->integer('total_rows')->default(0)->after('imported_cards_count');
            $table->integer('skipped_rows')->default(0)->after('total_rows');
            $table->integer('updated_cards_count')->default(0)->after('skipped_rows');
            $table->json('validation_errors')->nullable()->after('error_message');
            $table->timestamp('started_at')->nullable()->after('validation_errors');
            $table->timestamp('completed_at')->nullable()->after('started_at');
        });

        // Convert format column from enum to string to allow txt format
        DB::statement("ALTER TABLE deck_imports MODIFY COLUMN format VARCHAR(10) NOT NULL DEFAULT 'csv'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deck_imports', function (Blueprint $table) {
            $table->dropColumn([
                'original_filename',
                'file_path',
                'total_rows',
                'skipped_rows',
                'updated_cards_count',
                'validation_errors',
                'started_at',
                'completed_at',
            ]);
        });

        // Convert back to enum
        DB::statement("ALTER TABLE deck_imports MODIFY COLUMN format ENUM('apkg', 'csv') NOT NULL DEFAULT 'csv'");
    }
};
