<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/admin-dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'admin.only'])->name('dashboard');

Route::get('/faculty-dashboard', function () {
    return Inertia::render('Faculty/FacultyDashboard');
})->middleware(['auth', 'verified', 'admin'])->name('faculty.dashboard');

Route::get('/admin-dashboard/stats', [AdminController::class, 'getDashboardStats'])
    ->middleware(['auth', 'verified', 'admin.only'])
    ->name('dashboard.stats');

Route::get('/faculty-dashboard/stats', [FacultyController::class, 'getDashboardStats'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('faculty.dashboard.stats');

Route::get('/student-dashboard', [StudentDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('student.dashboard');

// Student-specific routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/student/equipment', [StudentDashboardController::class, 'equipmentCatalog'])->name('student.equipment.catalog');
    Route::get('/student/issued-equipment', [StudentDashboardController::class, 'issuedEquipment'])->name('student.equipment.issued');
    Route::get('/student/requested-equipment', [StudentDashboardController::class, 'requestedEquipment'])->name('student.equipment.requested');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Student reservation routes
    Route::middleware('verified')->group(function () {
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
        Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::post('/reservations/availability', [ReservationController::class, 'checkAvailability'])->name('reservations.availability');
    });

    // Admin routes (accessible only by admin users)
    Route::middleware('admin.only')->group(function () {
        Route::get('/admin/students', [AdminController::class, 'students'])->name('admin.students.index');
        Route::get('/admin/students/create', [AdminController::class, 'createStudent'])->name('admin.students.create');
        Route::post('/admin/students', [AdminController::class, 'storeStudent'])->name('admin.students.store');
        Route::put('/admin/students/{student}', [AdminController::class, 'updateStudent'])->name('admin.students.update');
        Route::delete('/admin/students/{student}', [AdminController::class, 'destroyStudent'])->name('admin.students.destroy');
        Route::get('/admin/students/data', [AdminController::class, 'getStudents'])->name('admin.students.data');

        // Department routes
        Route::get('/admin/departments', [DepartmentController::class, 'index'])->name('admin.departments.index');
        Route::get('/admin/departments/create', [DepartmentController::class, 'create'])->name('admin.departments.create');
        Route::post('/admin/departments', [DepartmentController::class, 'store'])->name('admin.departments.store');
        Route::put('/admin/departments/{department}', [DepartmentController::class, 'update'])->name('admin.departments.update');
        Route::delete('/admin/departments/{department}', [DepartmentController::class, 'destroy'])->name('admin.departments.destroy');
        Route::get('/admin/departments/active', [DepartmentController::class, 'getActiveDepartments'])->name('admin.departments.active');

        // Equipment routes
        Route::get('/admin/equipment', [EquipmentController::class, 'index'])->name('admin.equipment.index');
        Route::get('/admin/equipment/create', [EquipmentController::class, 'create'])->name('admin.equipment.create');
        Route::post('/admin/equipment', [EquipmentController::class, 'store'])->name('admin.equipment.store');
        Route::post('/admin/equipment/{equipment}', [EquipmentController::class, 'update'])->name('admin.equipment.update');
        Route::delete('/admin/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('admin.equipment.destroy');
        Route::get('/admin/equipment/data', [EquipmentController::class, 'getEquipment'])->name('admin.equipment.data');
        Route::get('/admin/equipment/category/{category?}', [EquipmentController::class, 'getByCategory'])->name('admin.equipment.category');

        // User Management routes
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/admin/users/{user}/promote', [UserController::class, 'promoteToFacultyStaff'])->name('admin.users.promote');
        Route::post('/admin/users/{user}/demote', [UserController::class, 'demoteToStudent'])->name('admin.users.demote');
        Route::get('/admin/users/data', [UserController::class, 'getData'])->name('admin.users.data');
        Route::get('/admin/users/stats', [UserController::class, 'getStats'])->name('admin.users.stats');

        // Reservation Management routes
        Route::get('/admin/issue-equipment', [AdminController::class, 'issueEquipment'])->name('admin.issue-equipment');
        Route::get('/admin/issued-equipment', [AdminController::class, 'issuedEquipment'])->name('admin.issued-equipment');
        Route::get('/admin/requested-equipment', [AdminController::class, 'requestedEquipment'])->name('admin.requested-equipment');
        Route::patch('/admin/reservations/{reservation}/approve', [AdminController::class, 'approveReservation'])->name('admin.reservations.approve');
        Route::patch('/admin/reservations/{reservation}/reject', [AdminController::class, 'rejectReservation'])->name('admin.reservations.reject');
        Route::patch('/admin/reservations/{reservation}/return', [AdminController::class, 'returnEquipment'])->name('admin.reservations.return');
    });

    // Faculty routes (accessible by both admin and faculty users)
    Route::middleware('admin')->group(function () {
        Route::get('/faculty/students', [FacultyController::class, 'students'])->name('faculty.students.index');
        Route::get('/faculty/students/create', [FacultyController::class, 'createStudent'])->name('faculty.students.create');
        Route::post('/faculty/students', [FacultyController::class, 'storeStudent'])->name('faculty.students.store');
        Route::put('/faculty/students/{student}', [FacultyController::class, 'updateStudent'])->name('faculty.students.update');
        Route::delete('/faculty/students/{student}', [FacultyController::class, 'destroyStudent'])->name('faculty.students.destroy');
        Route::get('/faculty/students/data', [FacultyController::class, 'getStudents'])->name('faculty.students.data');

        // Faculty Department routes
        Route::get('/faculty/departments', [DepartmentController::class, 'index'])->name('faculty.departments.index');
        Route::get('/faculty/departments/create', [DepartmentController::class, 'create'])->name('faculty.departments.create');
        Route::post('/faculty/departments', [DepartmentController::class, 'store'])->name('faculty.departments.store');
        Route::put('/faculty/departments/{department}', [DepartmentController::class, 'update'])->name('faculty.departments.update');
        Route::delete('/faculty/departments/{department}', [DepartmentController::class, 'destroy'])->name('faculty.departments.destroy');
        Route::get('/faculty/departments/active', [DepartmentController::class, 'getActiveDepartments'])->name('faculty.departments.active');

        // Faculty Equipment routes
        Route::get('/faculty/equipment', [FacultyController::class, 'equipmentIndex'])->name('faculty.equipment.index');
        Route::get('/faculty/equipment/create', [FacultyController::class, 'equipmentCreate'])->name('faculty.equipment.create');
        Route::post('/faculty/equipment', [FacultyController::class, 'equipmentStore'])->name('faculty.equipment.store');
        Route::post('/faculty/equipment/{equipment}', [FacultyController::class, 'equipmentUpdate'])->name('faculty.equipment.update');
        Route::delete('/faculty/equipment/{equipment}', [FacultyController::class, 'equipmentDestroy'])->name('faculty.equipment.destroy');
        Route::get('/faculty/equipment/data', [FacultyController::class, 'getEquipment'])->name('faculty.equipment.data');
        Route::get('/faculty/equipment/category/{category?}', [FacultyController::class, 'getEquipmentByCategory'])->name('faculty.equipment.category');

        // Faculty Reservation Management routes
        Route::get('/faculty/issue-equipment', [FacultyController::class, 'issueEquipment'])->name('faculty.issue-equipment');
        Route::get('/faculty/issued-equipment', [FacultyController::class, 'issuedEquipment'])->name('faculty.issued-equipment');
        Route::get('/faculty/requested-equipment', [FacultyController::class, 'requestedEquipment'])->name('faculty.requested-equipment');
        Route::patch('/faculty/reservations/{reservation}/approve', [FacultyController::class, 'approveReservation'])->name('faculty.reservations.approve');
        Route::patch('/faculty/reservations/{reservation}/reject', [FacultyController::class, 'rejectReservation'])->name('faculty.reservations.reject');
        Route::patch('/faculty/reservations/{reservation}/return', [FacultyController::class, 'returnEquipment'])->name('faculty.reservations.return');
    });
});

require __DIR__.'/auth.php';
