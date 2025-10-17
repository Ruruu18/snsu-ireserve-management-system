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
        // Add soft deletes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to equipment table
        Schema::table('equipment', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to reservations table
        Schema::table('reservations', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to reservation_items table
        Schema::table('reservation_items', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop soft deletes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Drop soft deletes from equipment table
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Drop soft deletes from reservations table
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Drop soft deletes from reservation_items table
        Schema::table('reservation_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
