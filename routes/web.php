<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentAppointmentController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeProfileController;
use App\Http\Controllers\Employee\AvailabilityController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\FirebaseAppointmentController;
use App\Http\Controllers\StudentProfileController;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentDetailsMail;
use App\Http\Controllers\WelcomePageController;
use App\Models\Appointment;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::get('/', [WelcomePageController::class, 'index'])->name('welcome');

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// ===============================
//    Student Dashboard Routes
// ===============================
Route::middleware(['auth', 'verified', 'client'])->prefix('student')
    ->name('student.')->group(function () {

    Route::get('/dashboard', [StudentDashboardController::class, 'index'])
        ->name('dashboard');
    Route::post('/appointments/{id}/cancel', [StudentDashboardController::class, 'cancel'])
        ->name('appointments.cancel');

    // Appointments Routes
    Route::get('/appointments', [StudentAppointmentController::class, 'index'])
        ->name('appointments.index');
    Route::post('/appointments', [StudentAppointmentController::class, 'store'])
        ->name('appointments.store');
    Route::get('/booked-slots', [StudentAppointmentController::class, 'getBookedSlots'])    
        ->name('appointments.booked-slots');
    Route::post('/appointments/update-status', [StudentAppointmentController::class, 'updateStatus'])
        ->name('appointments.update.status');
    Route::get('/staff', [StudentAppointmentController::class, 'showStaff'])
        ->name('appointments.staff');
    Route::get('/availability', [StudentAppointmentController::class, 'getAvailability'])
        ->name('availability');
    Route::get('/check-duplicate', [StudentAppointmentController::class, 'checkDuplicate'])
        ->name('appointments.check-duplicate');
    Route::get('/staff-days', [StudentAppointmentController::class, 'getStaffDays']);
    Route::get('/availability-dates', [StudentAppointmentController::class, 'getBookedDates']);

    
    // Calendar(special-days) Route
    Route::get('/special-days/json', [\App\Http\Controllers\Admin\SpecialDayController::class, 'getActiveSpecialDays'])
        ->name('student.special-days.json');

    Route::get('/special-days', [\App\Http\Controllers\StudentAppointmentController::class, 'getSpecialDays'])
    ->name('student.specialDays');

    // Send Email Route
    Route::post('/appointments/{appointment}/send-email', [StudentAppointmentController::class, 'sendEmail'])
        ->name('appointments.email');

    // DL Deatails Route
    Route::get('/appointments/{appointment}/download', [StudentAppointmentController::class, 'download'])
        ->name('appointments.download');

    // Profile Edit Route
    Route::get('/profile/edit', [StudentProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile/update', [StudentProfileController::class, 'update'])
        ->name('profile.update');
});


// ===============================
// ✅ Employee Routing
// ===============================
Route::middleware(['auth', 'verified', 'employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/appointments/upcoming', [EmployeeDashboardController::class, 'getUpcomingAppointments'])->name('appointments.upcoming');
    Route::post('/appointments/update-status', [EmployeeDashboardController::class, 'updateStatus'])->name('appointments.updateStatus');

    // Profile
    Route::get('/profile/edit', [EmployeeProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [EmployeeProfileController::class, 'update'])->name('profile.update');

    // Availability
    Route::get('/availability', [AvailabilityController::class, 'edit'])->name('availability.edit');
    Route::put('/availability', [AvailabilityController::class, 'update'])->name('availability.update');
});


// ===============================
//          Admin Routes
// ===============================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Calendar Route
    Route::get('appointments/calendar-data', [AppointmentController::class, 'getCalendarData']);
        Route::get('/appointments', [AppointmentController::class, 'index'])
            ->name('appointments.index');
        Route::post('appointments/update-status', [AppointmentController::class, 'updateStatus'])
            ->name('appointments.update-status');

        Route::get('/firebase-appointments', [FirebaseAppointmentController::class, 'index'])
            ->name('firebase.appointments.index');
        Route::get('/firebase-appointments/calendar-data', [FirebaseAppointmentController::class, 'getCalendarData'])
            ->name('firebase.appointments.calendar-data');
        Route::post('/firebase-appointments/update-status', [FirebaseAppointmentController::class, 'updateStatus'])
            ->name('firebase.appointments.update-status');

    // Reports Route
    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])
        ->name('reports.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Special Days Routes
    Route::resource('special-days', \App\Http\Controllers\Admin\SpecialDayController::class)
        ->except(['show']);

    // Calendar Data (special-days)
    Route::get('special-days/calendar-data', [\App\Http\Controllers\Admin\SpecialDayController::class, 'calendarData'])
        ->name('special-days.calendarData');

    // Trash and Restore
    Route::get('special-days-trash', [\App\Http\Controllers\Admin\SpecialDayController::class, 'trash'])
        ->name('special-days.trash');
    Route::post('special-days/{id}/restore', [\App\Http\Controllers\Admin\SpecialDayController::class, 'restore'])
        ->name('special-days.restore');
    Route::delete('special-days/{id}', [\App\Http\Controllers\Admin\SpecialDayController::class, 'destroy'])
        ->name('special-days.destroy');
    Route::delete('special-days/{id}/force-delete', [\App\Http\Controllers\Admin\SpecialDayController::class, 'forceDelete'])
        ->name('special-days.forceDelete');

    // Categories route
    Route::resource('categories', CategoryController::class);
        Route::get('categories-trash', [CategoryController::class, 'trashed'])
            ->name('categories.trashed');
        Route::put('categories/{id}/restore', [CategoryController::class, 'restore'])
            ->name('categories.restore');
        Route::delete('categories/{id}/force-delete', [CategoryController::class, 'forceDelete'])
            ->name('categories.force_delete');

    // Services route
    Route::resource('services', ServiceController::class);
        Route::get('services-trash', [ServiceController::class, 'trash'])
            ->name('services.trash');
        Route::put('services/{id}/restore', [ServiceController::class, 'restore'])
            ->name('services.restore');
        Route::delete('services/{id}/force-delete', [ServiceController::class, 'force_delete'])
            ->name('services.force_delete');

    // Users route
    Route::resource('users', UserController::class);
        Route::get('users-trash', [UserController::class, 'trashView'])
            ->name('users.trash');
        Route::put('users/{id}/restore', [UserController::class, 'restore'])
            ->name('users.restore');
        Route::delete('users/{id}/force-delete', [UserController::class, 'force_delete'])
            ->name('users.force_delete');
});

Route::get('/test-email', function () {
    $appointment = \App\Models\Appointment::latest()->first();

    if (!$appointment || !$appointment->user || !$appointment->user->email) {
        return 'No valid appointment or client email found.';
    }

    Mail::to($appointment->user->email)->send(new AppointmentDetailsMail($appointment));

    return 'Email sent to: ' . $appointment->user->email;
});

require __DIR__.'/auth.php';
