<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['prestataire_id','prix', 'description','categorie_id', 'sous_categorie_id', 'portfolio_id'];

    public function sousCategorie()
    {
        return $this->belongsTo(SousCategorie::class);
    }

    public function prestataire() {
        return $this->belongsTo(PrestataireService::class);
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'service_id');
    }

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class, 'portfolio_id');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

}
