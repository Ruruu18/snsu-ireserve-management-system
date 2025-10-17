<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users that belong to this department.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get active users in this department.
     */
    public function activeUsers(): HasMany
    {
        return $this->hasMany(User::class)->whereNotNull('email_verified_at');
    }

    /**
     * Get active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the department name with code.
     */
    public function getFullNameAttribute()
    {
        return $this->code ? "{$this->name} ({$this->code})" : $this->name;
    }

    /**
     * Get department statistics with caching.
     */
    public function getStatistics(): array
    {
        return Cache::remember("department_{$this->id}_stats", 300, function () {
            return [
                'total_users' => $this->users()->count(),
                'active_users' => $this->activeUsers()->count(),
                'student_count' => $this->users()->where('role', 'student')->count(),
                'faculty_count' => $this->users()->where('role', 'faculty_staff')->count(),
                'total_reservations' => Reservation::whereHas('user', function ($query) {
                    $query->where('department_id', $this->id);
                })->count(),
                'active_reservations' => Reservation::whereHas('user', function ($query) {
                    $query->where('department_id', $this->id);
                })->whereIn('status', ['pending', 'approved', 'issued'])->count(),
            ];
        });
    }

    /**
     * Clear department statistics cache.
     */
    public function clearStatisticsCache(): void
    {
        Cache::forget("department_{$this->id}_stats");
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($department) {
            $department->clearStatisticsCache();
        });

        static::deleted(function ($department) {
            $department->clearStatisticsCache();
        });
    }
}
