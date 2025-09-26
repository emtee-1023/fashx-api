
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sketch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('mockup_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('pattern_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['sketch_to_mockup', 'mockup_to_pattern', 'other']);
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->text('prompt')->nullable();
            $table->string('model_used')->nullable();
            $table->text('error_message')->nullable();
            $table->string('result_file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_jobs');
    }
};
