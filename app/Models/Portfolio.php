<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = ['prestataire_id', 'images', 'description'];

    protected $casts = [
        'images' => 'array',
    ];

    public function prestataire()
    {
        return $this->belongsTo(PrestataireService::class, 'prestataire_id');
    }

    public function service()
    {
        return $this->hasOne(Service::class, 'portfolio_id');
    }
}
