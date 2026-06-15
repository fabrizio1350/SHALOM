<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encomienda extends Model
{
    protected $table = 'encomiendas';
    protected $primaryKey = 'id_encomienda';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_encomienda',
        'remitente',
        'destinatario',
        'ciudad_destino',
        'peso',
        'dimensiones',
        'descripcion',
        'estado',
        'id_zona',
        'fecha_ingreso'
    ];

    // Pertenece a una zona
    public function zona()
    {
        return $this->belongsTo(Zona::class, 'id_zona');
    }

    // Tiene muchas alertas
    public function alertas()
    {
        return $this->hasMany(Alerta::class, 'id_encomienda', 'id_encomienda');
    }

    // Tiene muchos movimientos en historial
    public function historial()
    {
        return $this->hasMany(HistorialMovimiento::class, 'id_encomienda', 'id_encomienda');
    }
}