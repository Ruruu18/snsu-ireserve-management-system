<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'department_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the reservations for the user.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the department that the user belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get active reservations for the user.
     */
    public function activeReservations()
    {
        return $this->reservations()->whereIn('status', ['issued', 'return_requested']);
    }

    /**
     * Get pending reservations for the user.
     */
    public function pendingReservations()
    {
        return $this->reservations()->where('status', 'pending');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a student.
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is faculty staff.
     */
    public function isFacultyStaff()
    {
        return $this->role === 'faculty_staff';
    }

    /**
     * Check if user has admin-level access (admin or faculty_staff).
     */
    public function hasAdminAccess()
    {
        return in_array($this->role, ['admin', 'faculty_staff']);
    }

    /**
     * Get the dashboard route based on user role.
     */
    public function getDashboardRoute()
    {
        if ($this->isAdmin()) {
            return 'dashboard';
        } elseif ($this->isFacultyStaff()) {
            return 'faculty.dashboard';
        } else {
            return 'student.dashboard';
        }
    }
}
