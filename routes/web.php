<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\SectorController;
use App\Http\Controllers\ReturnProcess\ReturnProcessController;
use App\Http\Controllers\ReturnProcess\ReturnProcessFlowController;
use App\Http\Controllers\ReturnProcess\WorkflowController;
use App\Http\Controllers\Logistic\ApiTransportController;
use App\Jobs\RateLimitedTransportJob;
use App\Models\Nfe;
use App\Services\Transportadoras\SaoMiguelService;
use App\Http\Controllers\Logistic\EntregaController;
use App\Http\Controllers\Logistic\AgendamentoLogisticaController;
use App\Http\Controllers\Nfe\NfeEspelhoController;
// =====================================================
// 🔐 Página inicial → Login
// =====================================================
Route::get('/', fn() => view('auth.login'));


// =====================================================
// 🔐 Dashboard
// =====================================================
Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


// =====================================================
// 👤 Perfil do usuário
// =====================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// =====================================================
// ⚙️ Administração (Setores, Usuários, Níveis, Permissões)
// =====================================================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // === SETORES ===
    Route::resource('sectors', SectorController::class)->except(['show']);

    // === USUÁRIOS ===
    Route::get('/users', [RegisteredUserController::class, 'users'])->name('users');
    Route::post('/users', [RegisteredUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [RegisteredUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [RegisteredUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [RegisteredUserController::class, 'destroy'])->name('users.destroy');

    // === NÍVEIS ===
    Route::post('/levels', [SectorController::class, 'levelStore'])->name('levels.store');
    Route::get('/levels/{level}/edit', [SectorController::class, 'levelEdit'])->name('levels.edit');
    Route::patch('/levels/{level}', [SectorController::class, 'levelUpdate'])->name('levels.update');
    Route::delete('/levels/{level}', [SectorController::class, 'levelDestroy'])->name('levels.destroy');

    Route::get('/nfe-test/{id}', function ($id) {
        $nfe = \App\Models\Nfe::with([
            'itens.icms',
            'itens.ipi',
            'itens.pis',
            'itens.cofins',
            'itens.ibscbs',
            'ibscbsTot'
        ])->findOrFail($id);

        $dados = app(NfeEspelhoController::class)
            ->formatarDados($nfe);

        return view('nfe.espelho', ['nfe' => $dados]);
    });
});


// =====================================================
// 📦 PROCESSOS DE DEVOLUÇÃO / RECUSA (CRUD e VIEWS)
// =====================================================
Route::prefix('return-process')
    ->name('return.process.')
    ->middleware(['auth'])
    ->group(function () {

        // 🔹 Listar / Ver
        Route::get('/', [ReturnProcessController::class, 'index'])
            ->middleware('haspermission:return.process')
            ->name('index');

        Route::get('/data', [ReturnProcessController::class, 'data'])
            ->middleware('haspermission:return.process')
            ->name('data');

        Route::get('/{id}', [ReturnProcessController::class, 'show'])
            ->middleware('haspermission:return.process')
            ->name('show');

        // 🔹 Criar processo
        Route::get('/create', [ReturnProcessController::class, 'create'])
            ->middleware('haspermission:return.process')
            ->name('create');

        Route::post('/', [ReturnProcessController::class, 'store'])
            ->middleware('haspermission:return.process')
            ->name('store');

        // 🔹 Excluir processo
        Route::delete('/{id}', [ReturnProcessController::class, 'destroy'])
            ->middleware('haspermission:process.delete')
            ->name('destroy');
    });


// =====================================================
// 🔄 FLUXO — ETAPAS / AVANÇO / TIMELINE / FINALIZAÇÃO
// =====================================================
Route::prefix('return-process-flow')
    ->name('return.flow.')
    ->middleware(['auth'])
    ->group(function () {

        // Avançar etapa
        Route::post('/{id}/advance', [ReturnProcessFlowController::class, 'advance'])
            ->middleware('haspermission:return.process')
            ->name('advance');

        // Finalizar processo
        Route::post('/{id}/finalize', [ReturnProcessFlowController::class, 'finalize'])
            ->middleware('haspermission:return.process')
            ->name('finalize');

        // Timeline
        Route::get('/{id}/timeline', [ReturnProcessFlowController::class, 'timeline'])
            ->middleware('haspermission:return.process')
            ->name('timeline');
    });


// =====================================================
// ⚙️ WORKFLOW CONFIG (Administração de fluxos)
// =====================================================
Route::middleware(['auth', 'can:process.manage_config'])->prefix('admin/workflows')->group(function () {

    Route::get('/', [WorkflowController::class, 'index'])->name('workflows.index');

    Route::post('/update-order', [WorkflowController::class, 'updateStepOrder'])->name('workflows.updateOrder');
    Route::post('/add-step', [WorkflowController::class, 'storeStep'])->name('workflows.addStep');

    Route::post('/add-reason', [WorkflowController::class, 'addReason'])->name('workflows.addReason');
    Route::put('/update-reason/{id}', [WorkflowController::class, 'updateReason'])->name('workflows.updateReason');
    Route::delete('/delete-reason/{id}', [WorkflowController::class, 'deleteReason'])->name('workflows.deleteReason');

    Route::delete('/template/{id}', [WorkflowController::class, 'deleteTemplate'])->name('workflows.deleteTemplate');
    Route::put('/template/{id}', [WorkflowController::class, 'updateTemplate'])->name('workflows.updateTemplate');

    Route::get('/step/{id}/edit', [WorkflowController::class, 'editStep'])->name('workflows.editStep');
    Route::put('/step/{id}', [WorkflowController::class, 'updateStep'])->name('workflows.updateStep');

    Route::get('/{template}/steps/options', [WorkflowController::class, 'stepOptions']);
    Route::post('/add-template', [WorkflowController::class, 'storeTemplate'])->name('workflows.addTemplate');
    Route::get('/{template}/steps', [WorkflowController::class, 'steps'])->name('workflows.steps');
    Route::delete('/step/{id}', [WorkflowController::class, 'deleteStep'])->name('workflows.deleteStep');
    Route::post('/transport/sao-miguel', [ApiTransportController::class, 'checkSaoMiguel']);
});

Route::get('/entregas', [EntregaController::class, 'index'])
    ->name('transpNfes.index');

/*
|--------------------------------------------------------------------------
| LOGÍSTICA - AGENDAMENTOS
|--------------------------------------------------------------------------
*/
Route::prefix('logistica/agendamentos')->group(function () {

    // listagem
    Route::get('/', [AgendamentoLogisticaController::class, 'index'])
        ->name('logistica.agendamentos.index');

    // tela de criação
    Route::get('/create', [AgendamentoLogisticaController::class, 'create'])
        ->name('logistica.agendamentos.create');

    // buscar NFes por transportadora (AJAX)
    Route::get('/nfes', [AgendamentoLogisticaController::class, 'fetchNfes'])
        ->name('logistica.agendamentos.fetchNfes');

    // 🔥 CRIAR AGENDAMENTO (POST)
    Route::post('/', [AgendamentoLogisticaController::class, 'store'])
        ->name('logistica.agendamentos.store');
});

/*
|--------------------------------------------------------------------------
| NFE - ESPELHO EM LOTE
|--------------------------------------------------------------------------
*/
Route::post('/nfe/espelho/lote', [NfeEspelhoController::class, 'gerarLote'])
    ->name('nfe.espelho.lote');
require __DIR__ . '/auth.php';
