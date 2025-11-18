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
            $table->string('native_currency', 3)->default('USD')->after('remember_token');
            $table->decimal('recent_salary', 12, 2)->nullable()->after('native_currency');
            $table->longText('resume_text')->nullable()->after('recent_salary');
            $table->string('timezone')->default('UTC')->after('resume_text');
            $table->boolean('is_admin')->default(false)->after('timezone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['native_currency', 'recent_salary', 'resume_text', 'timezone', 'is_admin']);
        });
    }
};
