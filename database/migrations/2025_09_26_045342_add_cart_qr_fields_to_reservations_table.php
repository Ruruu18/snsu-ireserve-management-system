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
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('reservation_code')->unique()->nullable()->after('user_id');
            $table->text('notes')->nullable()->after('admin_notes');
            $table->text('qr_code_data')->nullable()->after('notes');
            $table->string('qr_code_path')->nullable()->after('qr_code_data');
            $table->timestamp('issued_at')->nullable()->after('qr_code_path');
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null')->after('issued_at');
            $table->foreignId('returned_by')->nullable()->constrained('users')->onDelete('set null')->after('returned_at');

            $table->index(['reservation_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['reservation_code']);
            $table->dropForeign(['issued_by']);
            $table->dropForeign(['returned_by']);
            $table->dropColumn([
                'reservation_code',
                'notes',
                'qr_code_data',
                'qr_code_path',
                'issued_at',
                'issued_by',
                'returned_by',
            ]);
        });
    }
};
