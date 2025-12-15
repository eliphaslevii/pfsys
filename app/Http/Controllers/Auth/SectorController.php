<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\Level;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    public function index()
    {
        $sectors = Sector::with('parent')->get();
        $levels = Level::with('sector')->get();

        $stats = [
            'total' => $sectors->count(),
            'active' => $sectors->where('is_active', true)->count(),
            'inactive' => $sectors->where('is_active', false)->count(),
            'with_children' => $sectors->whereNotNull('parent_id')->count(),
        ];

        $levelStats = [
            'total' => $levels->count(),
            'max' => $levels->max('authority_level'),
            'min' => $levels->min('authority_level'),
            'sectors' => $levels->pluck('sector_id')->unique()->count(),
        ];

        return view('auth.sectors', compact('sectors', 'levels', 'stats', 'levelStats'));
    }

    // === SECTORS ===
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:sectors,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:sectors,id',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        Sector::create($data);

        return response()->json(['success' => true, 'message' => 'Setor criado com sucesso!']);
    }


    public function edit(Sector $sector)
    {
        $parents = Sector::where('id', '!=', $sector->id)->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'sector' => $sector,
            'parents' => $parents
        ]);
    }


    public function update(Request $request, Sector $sector)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:sectors,name,' . $sector->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:sectors,id',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $sector->update($data);

        return response()->json(['success' => true, 'message' => 'Setor atualizado com sucesso!']);
    }


    public function destroy(Sector $sector)
    {
        $sector->delete();
        return response()->json(['success' => true, 'message' => 'Setor excluído com sucesso!']);
    }

    // === LEVELS ===
    public function levelStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'sector_id' => 'required|exists:sectors,id',
            'authority_level' => 'required|integer|min:0|max:100',
        ]);

        Level::create($data);

        return response()->json(['success' => true, 'message' => 'Nível criado com sucesso!']);
    }

    public function levelEdit(Level $level)
    {
        return response()->json(['success' => true, 'level' => $level]);
    }

    public function levelUpdate(Request $request, Level $level)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'sector_id' => 'required|exists:sectors,id',
            'authority_level' => 'required|integer|min:0|max:100',
        ]);

        $level->update($data);

        return response()->json(['success' => true, 'message' => 'Nível atualizado com sucesso!']);
    }

    public function levelDestroy(Level $level)
    {
        $level->delete();

        return response()->json(['success' => true, 'message' => 'Nível excluído com sucesso!']);
    }
}
