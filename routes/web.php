<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SectorController;
use App\Http\Controllers\ReturnProcess\ReturnProcessController;


// Página inicial → Login
Route::get('/', fn() => view('auth.login'));

// Dashboard principal
Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rotas de perfil (usuário autenticado)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 🔐 Rotas administrativas
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // === SETORES ===
    Route::get('/sectors', [SectorController::class, 'index'])->name('sectors.index');
    Route::post('/sectors', [SectorController::class, 'store'])->name('sectors.store');
    Route::get('/sectors/{sector}/edit', [SectorController::class, 'edit'])->name('sectors.edit');
    Route::patch('/sectors/{sector}', [SectorController::class, 'update'])->name('sectors.update');
    Route::delete('/sectors/{sector}', [SectorController::class, 'destroy'])->name('sectors.destroy');

    // === USUÁRIOS ===
    Route::get('/users', [RegisteredUserController::class, 'users'])->name('users');
    Route::post('/users', [RegisteredUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [RegisteredUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [RegisteredUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [RegisteredUserController::class, 'destroy'])->name('users.destroy');

    Route::post('/levels', [SectorController::class, 'levelStore'])->name('levels.store');
    Route::get('/levels/{level}/edit', [SectorController::class, 'levelEdit'])->name('levels.edit');
    Route::patch('/levels/{level}', [SectorController::class, 'levelUpdate'])->name('levels.update');
    Route::delete('/levels/{level}', [SectorController::class, 'levelDestroy'])->name('levels.destroy');
});
Route::prefix('return-process')->name('return.process.')->group(function () {
    Route::get('/', [ReturnProcessController::class, 'index'])->name('index');
    Route::get('/create', [ReturnProcessController::class, 'create'])->name('create');
    Route::get('/data', [ReturnProcessController::class, 'data'])->name('data');
    Route::get('/{id}', [ReturnProcessController::class, 'show'])->name('show');
    Route::post('/', [ReturnProcessController::class, 'store'])->name('store');
    Route::post('/{id}/update-step', [ReturnProcessStepController::class, 'update'])->name('update-step');
    Route::post('/{id}/reject', [ReturnProcessController::class, 'reject'])->name('reject');
    Route::post('/{id}/send-financeiro2', [ReturnProcessController::class, 'sendFinanceiro2'])->name('send-financeiro2');
    Route::delete('/{id}', [ReturnProcessController::class, 'destroy'])->name('destroy');
});


require __DIR__ . '/auth.php';
