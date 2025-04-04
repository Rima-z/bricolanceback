<?php

namespace App\Http\Controllers;

use App\Models\SousCategorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class SousCategorieController extends Controller
{
    
    public function index(Request $request)
{
    // Si un categorie_id est fourni dans la requête, filtrer par catégorie
    if ($request->has('categorie_id')) {
        $sousCategories = SousCategorie::where('categorie_id', $request->categorie_id)->get();
        return response()->json($sousCategories);
    }
    
    // Sinon retourner toutes les sous-catégories
    return response()->json(SousCategorie::all());
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'categorie_id' => 'required|exists:categories,id'
        ]);
        $sousCategorie = SousCategorie::create($data);
        return response()->json($sousCategorie, 201);
    }

    public function show($id)
    {
        $sousCategorie = SousCategorie::find($id);

        if (!$sousCategorie) {
            return response()->json(['message' => 'Sous-catégorie non trouvée'], 404);
        }

        return response()->json($sousCategorie);
    }


    public function update(Request $request, $id)
    {
        // Vérifier si la sous-catégorie existe
        $sousCategorie = SousCategorie::find($id);

        if (!$sousCategorie) {
            return response()->json(['message' => 'Sous-catégorie non trouvée'], 404);
        }

        // Validation des données
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255',
            'categorie_id' => 'sometimes|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mise à jour des données
        $sousCategorie->update($validator->validated());

        return response()->json([
            'message' => 'Sous-catégorie mise à jour avec succès',
            'sousCategorie' => $sousCategorie
        ], 200);
    }

    public function destroy($id)
    {
        $sousCategorie = SousCategorie::find($id);

        if (!$sousCategorie) {
            return response()->json(['message' => 'Sous-catégorie non trouvée'], 404);
        }

        $sousCategorie->delete();

        return response()->json(['message' => 'Sous-catégorie supprimée avec succès'], 200);
    }

}
