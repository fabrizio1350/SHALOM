<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'zonas';

    protected $fillable = [
        'nombre',
        'capacidad',
        'estado'
    ];

    // Una zona tiene muchas encomiendas
    public function encomiendas()
    {
        return $this->hasMany(Encomienda::class, 'id_zona');
    }
}