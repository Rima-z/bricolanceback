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

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Identifiants invalides'], 401);
            }

            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            // Retourner le token JWT
            return response()->json([
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => $user,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Impossible de créer le token'], 500);
        }
    }

    // Inscription d'un nouvel utilisateur
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'num_tlf' => 'required|string|max:20',
            'region' => 'nullable|string',
            'adresse' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:client,prestataire',
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

            if ($validatedData['role'] === 'prestataire') {
                $prestataire = PrestataireService::create([
                    'client_id' => $client->id,
                ]);

                $portfolio = Portfolio::create([
                    'prestataire_id' => $prestataire->id,
                    'description' => 'Portfolio par défaut',
                    'images' => [],
                ]);
            }

            // Générer un token JWT pour l'utilisateur
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Utilisateur enregistré avec succès',
                'user' => $user,
                'client' => $client,
                'prestataire' => $prestataire,
                'portfolio' => $portfolio,
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'inscription',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Récupérer l'utilisateur authentifié
    public function me()
    {
        try {
            $user = auth()->userOrFail();
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'name' => $user->name
                ],
                'client' => $user->client ? [
                    'id' => $user->client->id,
                    'nom' => $user->client->nom,
                    'prenom' => $user->client->prenom,
                    'email' => $user->client->email,
                    'num_tlf' => $user->client->num_tlf,
                    'region' => $user->client->region,
                    'adresse' => $user->client->adresse
                ] : null
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => $e->getMessage()
            ], 401);
        }
    }
    // Déconnexion
    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Déconnexion réussie']);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Erreur lors de la déconnexion'], 500);
        }
    }

    // Rafraîchir le token
    public function refresh()
    {
        try {
            $newToken = auth()->refresh();
            return response()->json([
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Impossible de rafraîchir le token'], 500);
        }
    }
}