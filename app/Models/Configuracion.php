<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    protected $fillable = [
        'tiempo_maximo_dias',
        'id_zona_reubicacion',
        'fecha_actualizacion',
        'id_admin'
    ];

    // Zona de reubicacion
    public function zonaReubicacion()
    {
        return $this->belongsTo(Zona::class, 'id_zona_reubicacion');
    }

    // Admin que hizo el ultimo cambio
    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin');
    }
}