<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ClientController extends Controller
{

    public function index()
    {
        return response()->json(Client::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'num_tlf' => 'required|string',
            'region' => 'nullable|string',
            'adresse' => 'nullable|string',
        ]);

        $client = Client::create($data);
        return response()->json($client, 201);
    }

    public function show($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client non trouvé'], 404);
        }

        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        // Trouver le client
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client non trouvé'], 404);
        }

        // Valider les données avec un Validator
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:clients,email,' . $id, // Correction ici
            'num_tlf' => 'sometimes|string',
            'region' => 'nullable|string',
            'adresse' => 'nullable|string',
        ]);

        // Si la validation échoue, renvoyer un message d'erreur
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Mise à jour des données
        $client->update($validator->validated());

        return response()->json([
            'message' => 'Client mis à jour avec succès',
            'client' => $client
        ], 200);
    }

    public function destroy($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client non trouvé'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Client supprimé avec succès'], 200);
    }


}
