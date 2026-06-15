<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'id_encomienda',
        'tipo',
        'estado',
        'fecha_generada'
    ];

    // Pertenece a una encomienda
    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class, 'id_encomienda', 'id_encomienda');
    }
}