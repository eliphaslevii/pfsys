<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SectorController;
use App\Http\Controllers\ReturnProcess\ReturnProcessController;
use App\Http\Controllers\ReturnProcess\ReturnProcessStepController;

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

Route::prefix('return-process')
    ->name('return.process.')
    ->middleware(['auth'])
    ->group(function () {

        // 🔹 Acesso geral (listar e visualizar)
        Route::get('/', [ReturnProcessController::class, 'index'])
            ->middleware('haspermission:return_process.view')
            ->name('index');

        Route::get('/data', [ReturnProcessController::class, 'getProcessesData'])
            ->middleware('haspermission:return_process.view')
            ->name('data');


        Route::get('/{id}', [ReturnProcessController::class, 'show'])
            ->middleware('haspermission:return_process.view')
            ->name('show');

        // 🔸 Criar processo (Comercial)
        Route::get('/create', [ReturnProcessController::class, 'create'])
            ->middleware('haspermission:return_process.create')
            ->name('create');

        Route::post('/', [ReturnProcessController::class, 'store'])
            ->middleware('haspermission:return_process.create')
            ->name('store');

        // 🔸 Atualizar etapa (Financeiro / Logística / Comercial)
        Route::post('/{id}/update-step', [ReturnProcessStepController::class, 'update'])
            ->middleware('haspermission:return_process.update_step')
            ->name('update-step');

        // 🔸 Rejeitar processo (Financeiro / Super Admin)
        Route::post('/{id}/reject', [ReturnProcessController::class, 'reject'])
            ->middleware('haspermission:return_process.reject')
            ->name('reject');

        // 🔸 Enviar para Financeiro 2 (Administrativo / Super)
        Route::post('/{id}/send-financeiro2', [ReturnProcessController::class, 'sendFinanceiro2'])
            ->middleware('haspermission:return_process.send_financeiro2')
            ->name('send-financeiro2');

        // 🔴 Excluir (Comercial / Super)
        Route::delete('/{id}', [ReturnProcessController::class, 'destroy'])
            ->middleware('haspermission:return_process.delete')
            ->name('destroy');
    });


require __DIR__ . '/auth.php';
