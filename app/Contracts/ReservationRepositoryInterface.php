<?php

namespace App\Contracts;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReservationRepositoryInterface
{
    /**
     * Get all reservations with pagination.
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find reservation by ID with relationships.
     */
    public function findWithRelations(int $id, array $relations = []): ?Reservation;

    /**
     * Get reservations by user ID.
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get reservations by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get pending reservations.
     */
    public function getPending(): Collection;

    /**
     * Get active reservations (issued/return_requested).
     */
    public function getActive(): Collection;

    /**
     * Create a new reservation.
     */
    public function create(array $data): Reservation;

    /**
     * Update a reservation.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a reservation.
     */
    public function delete(int $id): bool;

    /**
     * Get reservation by code.
     */
    public function findByCode(string $code): ?Reservation;

    /**
     * Get reservations for a specific date range.
     */
    public function getByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Get user's active reservation count.
     */
    public function getUserActiveCount(int $userId): int;

    /**
     * Get statistics for dashboard.
     */
    public function getStatistics(): array;
}