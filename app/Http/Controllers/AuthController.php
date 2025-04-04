<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Client;
use App\Models\PrestataireService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Portfolio;

class AuthController extends Controller
{
    // Connexion et génération du token
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['token' => $token]);
    }

    // Inscription d'un nouvel utilisateur (optionnel)
    public function register(Request $request)
    {
        // Validation des données d'entrée
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'num_tlf' => 'required|string|max:20',
            'region' => 'nullable|string',
            'adresse' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:client,prestataire', // Assurer que le rôle soit client ou prestataire
        ]);

        try {
            // Création de l'utilisateur
            $user = User::create([
                'name' => $validatedData['nom'] . ' ' . $validatedData['prenom'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
            ]);

            // Création du client
            $client = Client::create([
                'nom' => $validatedData['nom'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'num_tlf' => $validatedData['num_tlf'],
                'region' => $validatedData['region'],
                'adresse' => $validatedData['adresse'],
            ]);

            $prestataire = null;
            $portfolio = null;

            // Si le rôle est "prestataire", ajouter également en tant que prestataire
            if ($validatedData['role'] === 'prestataire') {
                $prestataire = PrestataireService::create([
                    'client_id' => $client->id, // Associer le prestataire au client
                ]);

                // Ajouter automatiquement un portfolio pour le prestataire
                $portfolio = Portfolio::create([
                    'prestataire_id' => $prestataire->id,
                    'description' => 'Portfolio par défaut',
                    'images' => [],
                ]);
            }

            // Générer un token JWT pour l'utilisateur
            $token = JWTAuth::fromUser($user);

            // Retourner la réponse JSON
            return response()->json([
                'message' => 'Utilisateur enregistré avec succès',
                'user' => $user,
                'client' => $client,
                'prestataire' => $prestataire,
                'portfolio' => $portfolio,
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            // Gestion des erreurs
            return response()->json([
                'error' => 'Une erreur est survenue lors de l\'inscription.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], 500);
        }

        return parent::render($request, $exception);
    }


    // Récupérer l'utilisateur authentifié
    public function me()
{
    $user = auth()->user();
    $client = Client::where('email', $user->email)->first();

    return response()->json([
        'user' => $user,
        'client' => $client
    ]);
}

    // Déconnexion et invalidation du token
    public function logout()
    {
        try {
            // Supprimer le token actuel sans l'invalider
            auth()->logout();

            return response()->json(['message' => 'Déconnexion réussie']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Erreur lors de la déconnexion'], 500);
        }
    }

}
