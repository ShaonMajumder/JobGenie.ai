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
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->index();
            $table->string('name');
            $table->string('scope')->index();
            $table->string('role');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('content');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['slug', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompts');
    }
};
