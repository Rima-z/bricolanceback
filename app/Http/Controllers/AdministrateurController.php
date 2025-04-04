<?php

namespace App\Http\Controllers;

use App\Models\Administrateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class AdministrateurController extends Controller
{
    public function index()
    {
        return response()->json(Administrateur::all());
    }
    public function store(Request $request)
    {
        $admin = Administrateur::create($request->all());
        return response()->json($admin, 201);
    }
    public function show($id)
    {
        $admin = Administrateur::find($id);

        if (!$admin) {
            return response()->json(['message' => 'Administrateur non trouvé'], 404);
        }

        return response()->json($admin);
    }

    public function update(Request $request, $id)
    {
        // Trouver l'administrateur
        $administrateur = Administrateur::find($id);

        if (!$administrateur) {
            return response()->json(['message' => 'Administrateur non trouvé'], 404);
        }

        // Valider les données avec un Validator
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:administrateurs,email,' . $id,
            'password' => 'sometimes|string|min:6',
        ]);

        // Si la validation échoue, renvoyer un message d'erreur
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mise à jour des données
        $administrateur->update($validator->validated());

        return response()->json([
            'message' => 'Administrateur mis à jour avec succès',
            'administrateur' => $administrateur
        ], 200);
    }

    public function destroy($id)
    {
        $administrateur = Administrateur::find($id);

        if (!$administrateur) {
            return response()->json(['message' => 'Administrateur non trouvé'], 404);
        }

        $administrateur->delete();

        return response()->json(['message' => 'Administrateur supprimé avec succès'], 200);
    }
}
