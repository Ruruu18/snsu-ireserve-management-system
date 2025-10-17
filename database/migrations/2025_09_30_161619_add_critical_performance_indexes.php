<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add critical indexes for performance
        $this->addIndexSafe('users', 'users_role_idx', 'role');
        $this->addIndexSafe('users', 'users_dept_idx', 'department_id');
        $this->addIndexSafe('reservations', 'reservations_status_idx', 'status');
        $this->addIndexSafe('reservations', 'reservations_user_idx', 'user_id');
        $this->addIndexSafe('reservation_items', 'res_items_equip_idx', 'equipment_id');
        $this->addIndexSafe('reservation_items', 'res_items_status_idx', 'status');
        $this->addIndexSafe('equipment', 'equipment_status_idx', 'status');
        $this->addIndexSafe('equipment', 'equipment_category_idx', 'category');
        $this->addIndexSafe('notifications', 'notifications_read_idx', 'is_read');
        $this->addIndexSafe('departments', 'departments_active_idx', 'is_active');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafe('users', 'users_role_idx');
        $this->dropIndexSafe('users', 'users_dept_idx');
        $this->dropIndexSafe('reservations', 'reservations_status_idx');
        $this->dropIndexSafe('reservations', 'reservations_user_idx');
        $this->dropIndexSafe('reservation_items', 'res_items_equip_idx');
        $this->dropIndexSafe('reservation_items', 'res_items_status_idx');
        $this->dropIndexSafe('equipment', 'equipment_status_idx');
        $this->dropIndexSafe('equipment', 'equipment_category_idx');
        $this->dropIndexSafe('notifications', 'notifications_read_idx');
        $this->dropIndexSafe('departments', 'departments_active_idx');
    }

    private function addIndexSafe(string $table, string $indexName, string $column): void
    {
        try {
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$column}`)");
        } catch (\Exception $e) {
            // Index might already exist, skip
        }
    }

    private function dropIndexSafe(string $table, string $indexName): void
    {
        try {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        } catch (\Exception $e) {
            // Index might not exist, skip
        }
    }
};
