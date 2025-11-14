<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SectorController;
use App\Http\Controllers\ReturnProcess\ReturnProcessController;
use App\Http\Controllers\ReturnProcess\ReturnProcessStepController;
use App\Http\Controllers\ReturnProcess\WorkflowController;
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
            ->middleware('haspermission:return.process')
            ->name('index');

        Route::get('/data', [ReturnProcessController::class, 'data'])
            ->middleware('haspermission:return.process')
            ->name('data');

        Route::get('/{id}', [ReturnProcessController::class, 'show'])
            ->middleware('haspermission:return.process')
            ->name('show');

        // 🔸 Criar processo (Comercial)
        Route::get('/create', [ReturnProcessController::class, 'create'])
            ->middleware('haspermission:return.process')
            ->name('create');

        Route::post('/', [ReturnProcessController::class, 'store'])
            ->middleware('haspermission:return.process')
            ->name('store');

        // 🔸 Atualizar etapa (Financeiro / Logística / Comercial)
        Route::post('/{id}/update-step', [ReturnProcessStepController::class, 'update'])
            ->middleware('haspermission:return.process')
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
            ->middleware('haspermission:process.delete')
            ->name('destroy');
    });

Route::middleware(['auth', 'can:coreflow.admin'])->prefix('admin/workflows')->group(function () {

    // 🔹 Tela principal
    Route::get('/', [WorkflowController::class, 'index'])->name('workflows.index');

    // 🔹 Etapas do fluxo (steps)
    Route::post('/update-order', [WorkflowController::class, 'updateStepOrder'])->name('workflows.updateOrder');
    Route::post('/add-step', [WorkflowController::class, 'storeStep'])->name('workflows.addStep');

    // 🔹 Motivos de devolução
    Route::post('/add-reason', [WorkflowController::class, 'addReason'])->name('workflows.addReason');
    Route::put('/update-reason/{id}', [WorkflowController::class, 'updateReason'])->name('workflows.updateReason');
    Route::delete('/delete-reason/{id}', [WorkflowController::class, 'deleteReason'])->name('workflows.deleteReason');
});

require __DIR__ . '/auth.php';
