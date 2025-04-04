<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Routes pour Authetification
Route::prefix('auth')->middleware('api')->group(function () {
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/me', [\App\Http\Controllers\AuthController::class, 'me']);
});
// Routes pour Administrateur
Route::get('/admins', [\App\Http\Controllers\AdministrateurController::class, 'index']);
Route::post('/admins', [\App\Http\Controllers\AdministrateurController::class, 'store']);
Route::get('/admins/{id}', [\App\Http\Controllers\AdministrateurController::class, 'show']);
Route::put('/admins/{id}', [\App\Http\Controllers\AdministrateurController::class, 'update']);
Route::delete('/admins/{id}', [\App\Http\Controllers\AdministrateurController::class, 'destroy']);

// Routes pour Services
Route::get('/services', [\App\Http\Controllers\ServiceController::class, 'index']);
// Seuls les prestataires authentifiés peuvent ajouter un service
Route::middleware(['auth:api', 'is_prestataire'])->post('/services', [\App\Http\Controllers\ServiceController::class, 'store']);
Route::get('/services/{id}', [\App\Http\Controllers\ServiceController::class, 'show']);
Route::middleware(['auth:api', 'is_prestataire'])->put('/services/{id}', [\App\Http\Controllers\ServiceController::class, 'update']);
Route::middleware(['auth:api', 'is_prestataire'])->delete('/services/{id}', [\App\Http\Controllers\ServiceController::class, 'destroy']);

// Routes pour Catégories
Route::get('/categories', [\App\Http\Controllers\CategorieController::class, 'index']);
Route::post('/categories', [\App\Http\Controllers\CategorieController::class, 'store']);
Route::get('/categories/{id}', [\App\Http\Controllers\CategorieController::class, 'show']);
Route::put('/categories/{id}', [\App\Http\Controllers\CategorieController::class, 'update']);
Route::delete('/categories/{id}', [\App\Http\Controllers\CategorieController::class, 'destroy']);

// Routes pour Commentaires
Route::get('/commentaires', [\App\Http\Controllers\CommentaireController::class, 'index']);
// Seuls les clients authentifiés peuvent ajouter un commentaire
Route::middleware('auth:api')->post('/commentaires', [\App\Http\Controllers\CommentaireController::class, 'store']);
//Route::post('/commentaires', [\App\Http\Controllers\CommentaireController::class, 'store']);
Route::get('/commentaires/{id}', [\App\Http\Controllers\CommentaireController::class, 'show']);
Route::middleware(['auth:api'])->put('/commentaires/{id}', [\App\Http\Controllers\CommentaireController::class, 'update']);
Route::middleware(['auth:api'])->delete('/commentaires/{id}', [\App\Http\Controllers\CommentaireController::class, 'destroy']);

// Routes pour Clients
Route::get('/clients', [\App\Http\Controllers\ClientController::class, 'index']);
Route::post('/clients', [\App\Http\Controllers\ClientController::class, 'store']);
Route::get('/clients/{id}', [\App\Http\Controllers\ClientController::class, 'show']);
Route::middleware('auth:api')->put('/clients/{id}', [\App\Http\Controllers\ClientController::class, 'update']);
Route::delete('/clients/{id}', [\App\Http\Controllers\ClientController::class, 'destroy']);

// Routes pour Sous-catégories
Route::get('/souscategories', [\App\Http\Controllers\SousCategorieController::class, 'index']);
Route::post('/souscategories', [\App\Http\Controllers\SousCategorieController::class, 'store']);
Route::get('/souscategories/{id}', [\App\Http\Controllers\SousCategorieController::class, 'show']);
Route::put('/souscategories/{id}', [\App\Http\Controllers\SousCategorieController::class, 'update']);
Route::delete('/souscategories/{id}', [\App\Http\Controllers\SousCategorieController::class, 'destroy']);

// Routes pour Prestataires
Route::get('/prestataires', [\App\Http\Controllers\PrestataireServiceController::class, 'index']);
Route::post('/prestataires', [\App\Http\Controllers\PrestataireServiceController::class, 'store']);
Route::get('/prestataires/{id}', [\App\Http\Controllers\PrestataireServiceController::class, 'show']);
Route::put('/prestataires/{id}', [\App\Http\Controllers\PrestataireServiceController::class, 'update']);
Route::delete('/prestataires/{id}', [\App\Http\Controllers\PrestataireServiceController::class, 'destroy']);
Route::middleware(['auth:api', 'is_prestataire'])->delete('/prestataires/{id}/portfolio', [\App\Http\Controllers\PrestataireServiceController::class, 'deletePortfolio']);
Route::middleware(['auth:api', 'is_prestataire'])->put('/prestataires/{id}/portfolio', [\App\Http\Controllers\PrestataireServiceController::class, 'updatePortfolio']);
