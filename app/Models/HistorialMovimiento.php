<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialMovimiento extends Model
{
    protected $table = 'historial_movimientos';
    public $timestamps = false;

    protected $fillable = [
        'id_encomienda',
        'estado_anterior',
        'estado_nuevo',
        'observacion',
        'id_usuario',
        'id_padre',
        'created_at'
    ];

    // Pertenece a una encomienda
    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class, 'id_encomienda', 'id_encomienda');
    }

    // Pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    // Relación padre
    public function padre()
    {
        return $this->belongsTo(HistorialMovimiento::class, 'id_padre');
    }

    // Relación hijos
    public function hijos()
    {
        return $this->hasMany(HistorialMovimiento::class, 'id_padre');
    }
}