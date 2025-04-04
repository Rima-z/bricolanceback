<?php

namespace App\Http\Controllers;

use App\Models\Commentaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentaireController extends Controller
{
    public function index()
    {
        return response()->json(Commentaire::all());
    }

    public function store(Request $request)
    {
        // Valider les données d'entrée
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'texte' => 'required|string',
            'note' => 'required|integer|min:1|max:5',
            'date' => 'required|date',
            'state' => 'sometimes|integer'
        ]);

        // Si la validation échoue, renvoyer un message d'erreur
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Créer un nouveau commentaire
        $commentaire = Commentaire::create($validator->validated());

        return response()->json($commentaire, 201);
    }

    public function show($id)
    {
        $commentaire = Commentaire::find($id);

        if (!$commentaire) {
            return response()->json(['message' => 'Commentaire non trouvé'], 404);
        }

        return response()->json($commentaire);
    }


    public function update(Request $request, $id)
    {
        // Trouver le commentaire
        $commentaire = Commentaire::find($id);

        if (!$commentaire) {
            return response()->json(['message' => 'Commentaire non trouvé'], 404);
        }

        // Valider les données
        $validator = Validator::make($request->all(), [
            'texte' => 'sometimes|string',
            'note' => 'sometimes|integer|min:1|max:5',
            'state' => 'sometimes|integer'
        ]);

        // Si la validation échoue, renvoyer un message d'erreur
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mettre à jour les données
        $commentaire->update($validator->validated());

        return response()->json([
            'message' => 'Commentaire mis à jour avec succès',
            'commentaire' => $commentaire
        ], 200);
    }

    public function destroy($id)
    {
        // Trouver le commentaire
        $commentaire = Commentaire::find($id);
        if (!$commentaire) {
            return response()->json(['message' => 'Commentaire non trouvé'], 404);
        }

        // Supprimer le commentaire
        $commentaire->delete();

        return response()->json(['message' => 'Commentaire supprimé avec succès'], 200);
    }
}
