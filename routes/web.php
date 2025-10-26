<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WorkPhaseController;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::prefix('admin')->group(function () {
        // Name routes as users.* while keeping URL under /admin/users
        Route::resource('users', UserController::class)->names('users');
    });
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

// Pagina principale con Vue
Route::get('/work-phases', [WorkPhaseController::class, 'index'])->name('workphases.index');

// API per Vue: restituisce dati JSON
Route::get('/api/work-phases', [WorkPhaseController::class, 'list'])->name('workphases.list');

// API per salvare i selezionati (se necessario)
Route::post('/api/work-phases/confirm', [WorkPhaseController::class, 'confirm'])->name('workphases.confirm');

require __DIR__.'/auth.php';
