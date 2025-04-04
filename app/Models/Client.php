<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'prenom', 'email', 'num_tlf', 'region', 'adresse'];

    public function prestataire()
    {
        return $this->hasOne(PrestataireService::class, 'client_id');
    }
    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'client_id');
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($client) {
            // Supprimer l'utilisateur lié au client
            User::where('email', $client->email)->delete();
        });
    }

}
