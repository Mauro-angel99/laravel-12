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
    return view('welcome');
})->middleware(['auth', 'verified'])->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/order-list', function () {
    // *** 1. RECUPERO DATI SENZA MODELLO (Usa Facade DB) ***

    try {
        // Specifica la connessione MSSQL (sqlsrv_gestionale) e interroga la tabella
        $dati_cab = DB::connection('sqlsrv_gestionale')
                      ->select('SELECT TOP 5 RECORD_ID, FLASS, FLDES FROM dbo.A01_ORD_FAS');
        // DB::select restituisce un array di oggetti standard, non una Collection di Eloquent.

    } catch (\Exception $e) {
        // Se c'è un errore (es. driver non caricato, credenziali sbagliate), mostra l'errore
        $dati_cab = null;
        // In un ambiente reale non faresti così, ma è utile per il debug
        session()->flash('db_error', $e->getMessage()); 
    }

    return view('order-list', [
        'dati_tab_cab' => $dati_cab
    ]);
})->middleware(['auth', 'verified'])->name('order-list');

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
