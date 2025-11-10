<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Level;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller
{
    public function users()
    {
        $users = User::with(['sector', 'level'])->get();
        $sectors = Sector::all();
        $levels = Level::with('sector')->get();

        // 🔹 Adiciona os números dos cards
        $stats = [
            'total' => User::count(),
            'active' => User::where('active', 1)->count(),
            'inactive' => User::where('active', 0)->count(),
            'sectors' => Sector::count(),
        ];

        return view('auth.register', compact('users', 'sectors', 'levels', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
            'sector_id' => 'required|exists:sectors,id',
            'level_id' => 'required|exists:levels,id',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Usuário criado com sucesso!',
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $user->load(['sector', 'level']); // 🔹 carrega relações

        $sectors = Sector::all(['id', 'name']);
        $levels = Level::with('sector')->get(['id', 'name', 'sector_id']);

        return response()->json([
            'success' => true,
            'user' => $user,
            'sectors' => $sectors,
            'levels' => $levels,
        ]);
    }

    public function update(Request $request, User $user)
    {
        \Log::info('📥 Dados recebidos no update:', $request->all());

        try {
            // ✅ Validação dos campos básicos
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'sector_id' => 'required|exists:sectors,id',
                'level_id' => 'required|exists:levels,id',
            ]);

            // ✅ Converte o checkbox "active"
            // Converte 'on', '1', true → 1 | null, false, 'off' → 0
            $data['active'] = $request->boolean('active');

            // ✅ Atualiza o usuário
            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso!',
                'user' => $user
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // 🛑 Retorna erro de validação (422)
            return response()->json([
                'success' => false,
                'message' => 'Falha de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            // 🚨 Log e erro genérico (500)
            \Log::error('Erro ao atualizar usuário: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'Usuário excluído com sucesso!',
        ]);
    }
}
