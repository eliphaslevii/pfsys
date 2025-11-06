<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\AjaxController;
use App\Models\Sector;
use Illuminate\Http\Request;

class SectorController extends AjaxController
{
    public function index()
    {
        $sectors = Sector::orderBy('name')->get();
        return view('auth.sectors', compact('sectors'));
    }

    public function store(Request $request)
    {

        try {
            $data = $request->validate([
                'name' => 'required|string|max:100|unique:sectors,name',
                'description' => 'nullable|string',
                'is_active' => 'nullable',
            ]);
            $data['is_active'] = $request->boolean('is_active');

            $sector = Sector::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Setor criado com sucesso!',
                'sector' => $sector
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Pega só a primeira mensagem de erro
            $firstError = collect($e->validator->errors()->all())->first();

            return response()->json([
                'success' => false,
                'message' => $firstError ?? 'Erro de validação.'
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar setor: ' . $e->getMessage()
            ], 500);
        }
    }


    public function edit(Sector $sector)
    {
        return response()->json([
            'success' => true,
            'message' => 'Setor carregado com sucesso!',
            'sector' => $sector
        ]);
    }

    public function update(Request $request, Sector $sector)
    {

        $data = $request->validate([
            'name' => 'required|string|max:100|unique:sectors,name,' . $sector->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $sector->update($data);

        return $this->ajaxResponse(true, 'Setor atualizado com sucesso!', $sector);
    }

    public function destroy(Sector $sector)
    {
        if ($sector->users()->exists()) {
            return $this->ajaxResponse(false, 'Existem usuários vinculados a este setor.', null, 422);
        }

        $sector->delete();
        return $this->ajaxResponse(true, 'Setor excluído com sucesso!');
    }
}
