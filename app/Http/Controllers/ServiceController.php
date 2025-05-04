<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    // Récupérer tous les services
    public function index(Request $request)
{
    $query = Service::with([
        'prestataire.client', 
        'categorie', 
        'sousCategorie',
        'commentaires',
        'portfolio'
    ]);

    // Ajout des filtres
    if ($request->has('categorie_id')) {
        $query->where('categorie_id', $request->categorie_id);
    }

    if ($request->has('sous_categorie_id')) {
        $query->where('sous_categorie_id', $request->sous_categorie_id);
    }

    return $query->get();
}
    // Ajouter un nouveau service
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
            'description' => 'required|string',
            'categorie_id' => 'required|exists:categories,id',
            'sous_categorie_id' => 'required|exists:sous_categories,id',
            'portfolio_images' => 'sometimes|array',
            'portfolio_images.*' => 'string',
            'portfolio_description' => 'nullable|string',
        ]);
    
        // Créer un portfolio
        $portfolio = \App\Models\Portfolio::create([
            'prestataire_id' => $prestataire_id,
            'images' => $validatedData['portfolio_images'] ?? [],
            'description' => $validatedData['portfolio_description'] ?? null,
        ]);
    
        // Créer le service
        $service = Service::create([
            'prestataire_id' => $prestataire_id,
            'prix' => $validatedData['prix'],
            'description' => $validatedData['description'],
            'categorie_id' => $validatedData['categorie_id'],
            'sous_categorie_id' => $validatedData['sous_categorie_id'],
            'portfolio_id' => $portfolio->id,
        ]);
    
        return response()->json([
            'message' => 'Service et portfolio créés avec succès',
            'service' => $service->load('categorie', 'sousCategorie', 'portfolio')
        ], 201);
    }
    
    // Récupérer un service spécifique
    public function show($id)
    {
        return Service::with([
            'prestataire.client',
            'portfolio',
            'categorie',
            'sousCategorie'
        ])->findOrFail($id);
    }

    // Modifier un service
    public function update(Request $request, $id)
    {
        // Trouver le service
        $service = Service::with('portfolio')->find($id);

        if (!$service) {
            return response()->json(['message' => 'Service non trouvé'], 404);
        }

        // Valider les données
        $validatedData = $request->validate([
            'prix' => 'sometimes|numeric',
            'description' => 'sometimes|string',
            'categorie_id' => 'sometimes|exists:categories,id',
            'sous_categorie_id' => 'sometimes|exists:sous_categories,id',
            'portfolio_images' => 'sometimes|array',
            'portfolio_images.*' => 'string',
            'portfolio_description' => 'nullable|string',
        ]);

        // Mettre à jour le service
        $service->update([
            'prix' => $validatedData['prix'] ?? $service->prix,
            'description' => $validatedData['description'] ?? $service->description,
            'categorie_id' => $validatedData['categorie_id'] ?? $service->categorie_id,
            'sous_categorie_id' => $validatedData['sous_categorie_id'] ?? $service->sous_categorie_id,
        ]);

        // Mettre à jour le portfolio si nécessaire
        if ($service->portfolio && (isset($validatedData['portfolio_images']) || isset($validatedData['portfolio_description']))) {
            $service->portfolio->update([
                'images' => $validatedData['portfolio_images'] ?? $service->portfolio->images,
                'description' => $validatedData['portfolio_description'] ?? $service->portfolio->description,
            ]);
        }

        return response()->json([
            'message' => 'Service mis à jour avec succès',
            'service' => $service->load('categorie', 'sousCategorie', 'portfolio')
        ]);
    }

    // Supprimer un service
    public function destroy($id)
    {
        $service = Service::with('portfolio')->find($id);

        if (!$service) {
            return response()->json(['message' => 'Service non trouvé'], 404);
        }

        // Supprimer le portfolio associé si il existe
        if ($service->portfolio) {
            $service->portfolio->delete();
        }

        $service->delete();

        return response()->json(['message' => 'Service supprimé avec succès'], 200);
    }
    
    // Récupérer les services d'un prestataire
    public function getByPrestataire(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

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

        $services = Service::with(['categorie', 'sousCategorie', 'portfolio'])
            ->where('prestataire_id', $user->client->prestataire->id)
            ->get();

        return response()->json($services);
    }
}