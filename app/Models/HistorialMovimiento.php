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
        'created_at'
    ];

    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class, 'id_encomienda', 'id_encomienda');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}