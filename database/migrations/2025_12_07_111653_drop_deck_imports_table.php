<?php

declare(strict_types=1);

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
        Schema::dropIfExists('deck_imports');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('deck_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deck_id')->nullable()->constrained()->onDelete('set null');
            $table->string('filename');
            $table->string('original_filename')->nullable();
            $table->string('file_path')->nullable();
            $table->string('format')->default('csv');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('imported_cards_count')->default(0);
            $table->integer('total_rows')->default(0);
            $table->integer('skipped_rows')->default(0);
            $table->integer('updated_cards_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('validation_errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }
};
