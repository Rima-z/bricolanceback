<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestataireService extends Model
{
    use HasFactory;

    protected $table = 'prestataire_services';
    protected $fillable = ['client_id'];//na7it: , 'portfolio'

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'prestataire_id');
    }

    public function portfolio() {
        return $this->hasOne(Portfolio::class, 'prestataire_id');//zidt :, 'prestataire_id'
    }
}
