<?php

namespace App\Http\Controllers;

use App\Models\PrestataireService;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrestataireServiceController extends Controller
{
    /**
     * Récupérer tous les prestataires avec leur portfolio
     */
    public function index()
    {
        return response()->json(PrestataireService::with('portfolio')->get());
    }

    /**
     * Créer un prestataire avec un portfolio (optionnel)
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'portfolio.description' => 'nullable|string',
            'portfolio.images' => 'nullable|array',
            'portfolio.images.*' => 'url' // Chaque image doit être une URL valide
        ]);

        // Création du prestataire
        $prestataire = PrestataireService::create([
            'client_id' => $validatedData['client_id']
        ]);

        // Création du portfolio si fourni
        if (!empty($validatedData['portfolio'])) {
            $portfolio = new Portfolio([
                'description' => $validatedData['portfolio']['description'] ?? null,
                'images' => $validatedData['portfolio']['images'] ?? []
            ]);
            $prestataire->portfolio()->save($portfolio);
        }

        return response()->json([
            'message' => 'Prestataire et portfolio créés avec succès',
            'prestataire' => $prestataire->load('portfolio')
        ], 201);
    }

    /**
     * Afficher un prestataire avec son portfolio
     */
    public function show($id)
    {
        $prestataire = PrestataireService::with('portfolio')->find($id);

        if (!$prestataire) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        return response()->json($prestataire);
    }

    /**
     * Mettre à jour un prestataire et son portfolio
     */
    public function update(Request $request, $id)
    {
        $prestataire = PrestataireService::with('portfolio')->find($id);

        if (!$prestataire) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        $validatedData = $request->validate([
            'client_id' => 'sometimes|exists:clients,id',
            'portfolio.description' => 'sometimes|string',
            'portfolio.images' => 'sometimes|array',
            'portfolio.images.*' => 'url'
        ]);

        // Mise à jour du prestataire si nécessaire
        if (isset($validatedData['client_id'])) {
            $prestataire->update(['client_id' => $validatedData['client_id']]);
        }

        // Mise à jour ou création du portfolio
        if (isset($validatedData['portfolio'])) {
            $portfolioData = [
                'description' => $validatedData['portfolio']['description'] ?? null,
                'images' => $validatedData['portfolio']['images'] ?? []
            ];

            $prestataire->portfolio()->updateOrCreate(
                ['prestataire_id' => $prestataire->id], // Condition pour vérifier si le portfolio existe
                $portfolioData // Données à mettre à jour ou à insérer
            );
        }

        return response()->json([
            'message' => 'Prestataire et portfolio mis à jour avec succès',
            'prestataire' => $prestataire->load('portfolio')
        ]);
    }


    /**
     * Supprimer un prestataire et son portfolio
     */
    public function destroy($id)
    {
        $prestataire = PrestataireService::find($id);

        if (!$prestataire) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        $prestataire->delete();

        return response()->json(['message' => 'Prestataire supprimé avec succès'], 200);
    }

    public function updatePortfolio(Request $request, $id)
    {
        // Trouver le portfolio du prestataire via l'ID dans l'URL
        $portfolio = Portfolio::where('prestataire_id', $id)->first();

        if (!$portfolio) {
            return response()->json(['message' => 'Portfolio non trouvé'], 404);
        }

        // Validation des données envoyées
        $validatedData = $request->validate([
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'url' // Vérifier que chaque image est une URL valide
        ]);

        // Mettre à jour le portfolio
        $portfolio->update($validatedData);

        return response()->json([
            'message' => 'Portfolio mis à jour avec succès',
            'portfolio' => $portfolio
        ], 200);
    }



    /**
     * Supprimer un portfolio uniquement (sans supprimer le prestataire)
     */
    public function deletePortfolio($id)
    {
        $portfolio = Portfolio::where('prestataire_id', $id)->first();

        if (!$portfolio) {
            return response()->json(['message' => 'Portfolio non trouvé'], 404);
        }

        $portfolio->delete();

        return response()->json(['message' => 'Portfolio supprimé avec succès'], 200);
    }

}
