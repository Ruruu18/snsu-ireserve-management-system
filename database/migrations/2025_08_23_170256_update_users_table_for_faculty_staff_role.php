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
            // Update the role column comment to reflect new faculty_staff role
            $table->string('role')->default('student')->comment('Role: admin, student, faculty_staff')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to original comment
            $table->string('role')->default('student')->comment('Role: admin, student')->change();
        });
    }
};
