<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WorkPhaseController;
use App\Http\Controllers\WorkPhaseAssignmentController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\UserController as ApiUserController;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::prefix('admin')->group(function () {
        // Name routes as users.* while keeping URL under /admin/users
        Route::resource('users', UserController::class)->names('users');
    });
});

// API routes
Route::middleware(['auth'])->group(function () {
    Route::get('/api/users', [ApiUserController::class, 'index']);
});

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    // Pagina principale con Vue
    Route::get('/work-phases', [WorkPhaseController::class, 'index'])->name('workphases.index');

    // API per Vue: restituisce dati JSON
    Route::get('/api/work-phases', [WorkPhaseController::class, 'list'])->name('workphases.list');

    // API per assegnare i selezionati
    Route::post('/api/work-phases/assign', [WorkPhaseController::class, 'assign'])->name('workphases.assign');
});

// Rotta per la vista (Vue)
Route::middleware('auth')->group(function () {
    Route::get('/assigned-work-phases', [WorkPhaseAssignmentController::class, 'index'])
        ->name('assigned-work-phases.index');

    // API per restituire i dati JSON
    Route::get('/api/assigned-work-phases', [WorkPhaseAssignmentController::class, 'list'])
        ->name('assigned-work-phases.list');
});

// Warehouse routes
Route::middleware('auth')->group(function () {
    Route::get('/warehouse', [WarehouseController::class, 'index'])
        ->name('warehouse.index');

    // API per restituire i dati JSON
    Route::get('/api/warehouse', [WarehouseController::class, 'list'])
        ->name('warehouse.list');
});

// Settings routes
Route::middleware('auth')->group(function () {
    Route::get('/settings/general', function () {
        return view('settings.general');
    })->name('settings.general');
});


require __DIR__.'/auth.php';
