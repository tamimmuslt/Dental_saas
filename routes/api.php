<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\OdontogramController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClinicalSessionController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InvoiceController;


// راوتات عامة
Route::post('/login', [AuthController::class, 'login']);

// راوتات محمية
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Patients (إدارة المرضى)
    Route::get('/patients', [PatientController::class, 'index']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    
    // Odontogram (خريطة الأسنان السريرية)
    Route::get('/patients/{patientId}/odontogram', [OdontogramController::class, 'getPatientMap']);
    Route::post('/patients/{patientId}/odontogram', [OdontogramController::class, 'updateTooth']);
    
    // Appointments (إدارة الجدولة والمواعيد)

    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::put('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);

    // Clinical Sessions (الجلسات العلاجية والروشتات)
    Route::get('/patients/{patientId}/sessions', [ClinicalSessionController::class, 'getPatientSessions']);
    Route::post('/sessions', [ClinicalSessionController::class, 'store']);

    // Invoices (الفواتير والماليات)
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);

    // Expenses (المصاريف)
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);

    // Inventory (المخزون والتنبيهات)
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory', [InventoryController::class, 'store']);
});