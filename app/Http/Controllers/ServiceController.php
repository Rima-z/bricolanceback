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
        // Récupérer l'utilisateur authentifié
        $user = auth()->user();
    
        // Vérifier si l'utilisateur a un prestataire
        if (!$user->client || !$user->client->prestataire) {
            return response()->json(['error' => 'Utilisateur non associé à un prestataire'], 400);
        }
    
        // Utiliser l'ID du prestataire de l'utilisateur
        $prestataire_id = $user->client->prestataire->id;
    
        // Valider les autres données
        $validatedData = $request->validate([
            'prix' => 'required|numeric',
            'description' => 'nullable|string',
            'categorie_id' => 'required|exists:categories,id',
            'sous_categorie_id' => 'required|exists:sous_categories,id',
            'portfolio_images' => 'required|array',
            'portfolio_images.*' => 'string',
            'portfolio_description' => 'nullable|string',
        ]);
    
        // Créer un portfolio
        $portfolio = \App\Models\Portfolio::create([
            'prestataire_id' => $prestataire_id,  // Utiliser l'ID du prestataire authentifié
            'images' => $validatedData['portfolio_images'],
            'description' => $validatedData['portfolio_description'] ?? null,
        ]);
    
        // Créer le service
        $service = Service::create([
            'prestataire_id' => $prestataire_id,  // Utiliser l'ID du prestataire authentifié
            'prix' => $validatedData['prix'],
            'description' => $validatedData['description'],
            'categorie_id' => $validatedData['categorie_id'],
            'sous_categorie_id' => $validatedData['sous_categorie_id'],
            'portfolio_id' => $portfolio->id,  // Associer le portfolio créé
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
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié'], 401);
    }

    // Chargez les relations nécessaires en une seule requête
    $user->load('client.prestataire.services');
    
    if (!$user->client) {
        return response()->json([
            'message' => 'Profil client non trouvé',
            'has_prestataire' => false
        ], 404);
    }

    if (!$user->client->prestataire) {
        return response()->json([
            'message' => 'Prestataire non trouvé',
            'has_prestataire' => false
        ], 404);
    }

    // Récupérez les services avec toutes les relations nécessaires
    $services = Service::with(['categorie', 'sousCategorie', 'portfolio'])
        ->where('prestataire_id', $user->client->prestataire->id)
        ->get();

    if ($services->isEmpty()) {
        return response()->json([
            'message' => 'Aucun service trouvé pour ce prestataire',
            'services' => []
        ], 200);
    }

    return response()->json($services);
}
}
