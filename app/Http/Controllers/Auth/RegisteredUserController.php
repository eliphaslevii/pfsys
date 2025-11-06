<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Level;
use App\Models\Sector;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View|RedirectResponse
    {
        $authUser = auth()->user();

        // Somente Super Admin (level_id = 1) pode acessar a criação de contas
        if (!$authUser || $authUser->level_id !== 1) {
            return redirect()->route('dashboard')
                ->with('error', 'Você não tem permissão para acessar essa página');

        }

        // Carregar setores e níveis
        $sectors = Sector::orderBy('name')->get();
        $levels = Level::with('sector')
            ->orderBy('sector_id')
            ->orderBy('authority_level', 'desc')
            ->get();

        return view('auth.register', compact('sectors', 'levels'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();

        // 🔐 Somente Super Admin
        if (!$authUser || $authUser->level_id !== 1) {
            return redirect()->back()->with('error', 'Acesso negado. Apenas Super Admin pode criar usuários.');
        }

        // 🧾 Validação - se falhar, Laravel redireciona automaticamente com erros
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)->letters()->numbers()->mixedCase()->symbols()
            ],
            'sector_id' => ['required', 'exists:sectors,id'],
            'level_id' => ['required', 'exists:levels,id'],
        ], [
            'name.required' => 'O nome completo é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'As senhas não coincidem.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'sector_id.required' => 'Selecione um setor.',
            'level_id.required' => 'Selecione um nível de acesso.',
        ]);

        // ⚙️ Verificação extra (nível e setor compatíveis)
        $level = Level::find($validated['level_id']);
        if (!$level || $level->sector_id != $validated['sector_id']) {
            return redirect()->back()->withInput()->with('error', 'O nível selecionado não pertence ao setor informado.');
        }

        // 💾 Criação do usuário
        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'sector_id' => $validated['sector_id'],
            'level_id' => $validated['level_id'],
        ]);

        event(new Registered($user));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso!'
            ], 201);
        }

        return redirect()->route('admin.users')->with('success', 'Usuário criado com sucesso!');

    }

    public function users()
    {
        $users = User::with(['sector', 'level'])->get();

        return view('auth.register', [
            'users' => User::with(['sector', 'level'])->get(),
            'sectors' => Sector::all(),
            'levels' => Level::with('sector')->get(),
            'stats' => [
                'total' => $users->count(),
                'active' => $users->where('active', true)->count(),
                'inactive' => $users->where('active', false)->count(),
                'sectors' => Sector::count(),
            ],
        ]);
    }

    public function toggleActive(User $user)
    {
        $user->active = !$user->active;
        $user->save();

        return redirect()->back()->with('success', 'Status do usuário atualizado.');
    }

    public function edit(User $user): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'user' => $user
        ]);
    }

    public function update(Request $request, User $user): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
                'sector_id' => ['required', 'exists:sectors,id'],
                'level_id' => ['required', 'exists:levels,id'],
                'active' => ['nullable', 'boolean'],
            ]);

            $validated['active'] = $request->boolean('active');

            $user->update($validated);

            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuário atualizado com sucesso!',
                    'user' => $user->load(['sector', 'level'])
                ]);
            }

            return redirect()
                ->route('admin.users')
                ->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $error = collect($e->validator->errors()->all())->first();
            return response()->json([
                'success' => false,
                'message' => $error ?? 'Erro de validação.',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function destroy(User $user, Request $request)
    {
        try {
            $user->delete();

            // Se for AJAX (fetch)
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuário excluído com sucesso!'
                ]);
            }

            // Fallback tradicional (quando não é fetch)
            return redirect()->route('admin.users')->with('success', 'Usuário excluído com sucesso!');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao excluir o usuário: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.users')->with('error', 'Erro ao excluir o usuário.');
        }
    }


}
