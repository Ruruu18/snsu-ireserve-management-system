<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Student\CartController;
use App\Http\Controllers\Admin\QRScannerController;
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

Route::get('/admin/reservations/recent', [AdminController::class, 'getRecentReservations'])
    ->middleware(['auth', 'verified', 'admin.only'])
    ->name('admin.reservations.recent');

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
    Route::post('/student/equipment/{reservation}/request-return', [StudentDashboardController::class, 'requestReturn'])->name('student.equipment.request-return');

    // Cart routes
    Route::get('/student/cart', [\App\Http\Controllers\Student\CartPageController::class, 'index'])->name('student.cart.index');
    Route::get('/student/cart/checkout', [CartController::class, 'checkout'])->name('student.cart.checkout');
    Route::post('/student/cart/add/{equipment}', [CartController::class, 'add'])->name('student.cart.add');
    Route::post('/student/cart/update/{equipment}', [CartController::class, 'update'])->name('student.cart.update');
    Route::delete('/student/cart/remove/{equipment}', [CartController::class, 'remove'])->name('student.cart.remove');
    Route::delete('/student/cart/clear', [CartController::class, 'clear'])->name('student.cart.clear');
    Route::post('/student/cart/process', [CartController::class, 'process'])->name('student.cart.process');

    // Handle GET requests to cart process (redirect to checkout)
    Route::get('/student/cart/process', function() {
        return redirect()->route('student.cart.checkout');
    });

    // QR View route
    Route::get('/student/reservation/{reservation}/qr', [CartController::class, 'viewQR'])->name('student.reservation.qr');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Student reservation routes (Legacy system - keeping for compatibility, redirects to cart)
    Route::middleware('verified')->group(function () {
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

        // Legacy routes redirected to cart system
        Route::get('/reservations/create', function() {
            return redirect()->route('student.cart.checkout');
        })->name('reservations.create');

        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');

        Route::post('/reservations/availability', function() {
            return response()->json(['reserved_slots' => []]);
        })->name('reservations.availability');
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
        Route::get('/admin/reservations', [AdminController::class, 'reservations'])->name('admin.reservations.index');
        Route::patch('/admin/reservations/{reservation}/approve', [AdminController::class, 'approveReservation'])->name('admin.reservations.approve');
        Route::patch('/admin/reservations/{reservation}/reject', [AdminController::class, 'rejectReservation'])->name('admin.reservations.reject');
        Route::patch('/admin/reservations/{reservation}/return', [AdminController::class, 'returnEquipment'])->name('admin.reservations.return');

        // QR Scanner routes
        Route::get('/admin/qr-scanner', [QRScannerController::class, 'index'])->name('admin.qr-scanner');
        Route::post('/admin/qr-scanner/scan', [QRScannerController::class, 'scan'])->name('admin.qr-scanner.scan');
        Route::post('/admin/qr-scanner/upload', [QRScannerController::class, 'uploadQR'])->name('admin.qr-scanner.upload');
        Route::post('/admin/qr-scanner/issue', [QRScannerController::class, 'issueEquipment'])->name('admin.qr-scanner.issue');
        Route::post('/admin/qr-scanner/return', [QRScannerController::class, 'returnEquipment'])->name('admin.qr-scanner.return');

        // Notification routes
        Route::get('/admin/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::patch('/admin/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.read');
        Route::patch('/admin/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.read-all');
        Route::get('/admin/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('admin.notifications.unread-count');
    });

    // Shared notification routes (accessible to all authenticated users)
    Route::post('/admin/notifications/cart-addition', [NotificationController::class, 'createCartNotification'])->name('admin.notifications.cart');

    // Faculty routes (reservation management only)
    Route::middleware('admin')->group(function () {
        // Faculty Reservation Management routes
        Route::get('/faculty/reservations', [FacultyController::class, 'reservationsIndex'])->name('faculty.reservations.index');
        Route::get('/faculty/issue-equipment', [FacultyController::class, 'issueEquipment'])->name('faculty.issue-equipment');
        Route::get('/faculty/issued-equipment', [FacultyController::class, 'issuedEquipment'])->name('faculty.issued-equipment');
        Route::get('/faculty/requested-equipment', [FacultyController::class, 'requestedEquipment'])->name('faculty.requested-equipment');
        Route::post('/faculty/reservations/{reservation}/approve', [FacultyController::class, 'approveReservation'])->name('faculty.reservations.approve');
        Route::post('/faculty/reservations/{reservation}/reject', [FacultyController::class, 'rejectReservation'])->name('faculty.reservations.reject');
        Route::post('/faculty/reservations/{reservation}/issue', [FacultyController::class, 'issueReservation'])->name('faculty.reservations.issue');
        Route::patch('/faculty/reservations/{reservation}/return', [FacultyController::class, 'returnEquipment'])->name('faculty.reservations.return');

        // Faculty QR Scanner (for reservation management)
        Route::get('/faculty/qr-scanner', [FacultyController::class, 'qrScanner'])->name('faculty.qr-scanner');
        Route::post('/faculty/qr-scanner/scan', [FacultyController::class, 'qrScan'])->name('faculty.qr-scanner.scan');
        Route::post('/faculty/qr-scanner/upload', [FacultyController::class, 'qrUpload'])->name('faculty.qr-scanner.upload');
    });
});

require __DIR__.'/auth.php';
