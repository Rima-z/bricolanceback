<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class ServiceController extends Controller
{
    //Récupérer tous les services
    public function index()
    {
        return Service::with([
            'prestataire.client', 
            'categorie', 
            'sousCategorie',
            'commentaires'
        ])->get();
    }
    

    //Ajouter un nouveau service

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'prestataire_id' => 'required|exists:prestataire_services,id',
            'prix' => 'required|numeric',
            'description' => 'nullable|string',
            'categorie_id' => 'required|exists:categories,id',
            'sous_categorie_id' => 'required|exists:sous_categories,id',
            'portfolio_images' => 'required|array',  // ⚡ Champ obligatoire pour les images du portfolio
            'portfolio_images.*' => 'string', // ⚡ Chaque image doit être une URL ou un chemin de fichier
            'portfolio_description' => 'nullable|string',
        ]);

        // 🔹 1️⃣ Créer un portfolio
        $portfolio = \App\Models\Portfolio::create([
            'prestataire_id' => $validatedData['prestataire_id'],
            'images' => $validatedData['portfolio_images'],
            'description' => $validatedData['portfolio_description'] ?? null,
        ]);

        // 🔹 2️⃣ Créer le service avec le portfolio associé
        $service = Service::create([
            'prestataire_id' => $validatedData['prestataire_id'],
            'prix' => $validatedData['prix'],
            'description' => $validatedData['description'],
            'categorie_id' => $validatedData['categorie_id'],
            'sous_categorie_id' => $validatedData['sous_categorie_id'],
            'portfolio_id' => $portfolio->id,  // 🔹 Association du portfolio créé
        ]);

        return response()->json([
            'message' => 'Service et portfolio créés avec succès',
            'service' => $service,
            'portfolio' => $portfolio
        ], 201);
    }

    //Récupérer un service spécifique

    public function show($id)
{
    $service = Service::with([
            'prestataire.client:email,id', // Ajout du client associé au prestataire
            'prestataire.portfolio', 
            'sousCategorie', 
            'categorie'
        ])
        ->with(['commentaires' => function ($query) {
            $query->whereNotNull('id');
        }])
        ->find($id);

    if (!$service) {
        return response()->json(['message' => 'Service non trouvé'], 404);
    }

    return response()->json($service);
}

    //modifier un service

    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service non trouvé'], 404);
        }

        $validatedData = $request->validate([
            'nom' => 'sometimes|string',
            'region' => 'sometimes|string',
            'num_tlf' => 'sometimes|string',
            'prix' => 'sometimes|numeric',
            'description' => 'sometimes|string',
            'categorie_id' => 'sometimes|exists:categories,id',
            'sous_categorie_id' => 'sometimes|exists:sous_categories,id'
        ]);

        $service->update($validatedData);

        return response()->json([
            'message' => 'Service mis à jour avec succès',
            'service' => $service
        ]);
    }

    //supprimer un service

    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service non trouvé'], 404);
        }

        $service->delete();

        return response()->json(['message' => 'Service supprimé avec succès'], 200);
    }
    
    // Ajoutez cette nouvelle méthode
public function getByPrestataire(Request $request)
{
    try {
        $user = auth()->userOrFail();
        
        if (!$user->client || !$user->client->prestataire) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        $services = Service::with(['categorie', 'sousCategorie', 'portfolio'])
            ->where('prestataire_id', $user->client->prestataire->id)
            ->get();

        return response()->json($services);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la récupération des services',
            'message' => $e->getMessage()
        ], 500);
    }
}
}
