<?php

namespace App\DataStructures;

use App\Models\Configuracion;

// Árbol de decisión — Labs S09-S12 de Algoritmos
// Clasifica paquetes por peso para asignar ESTANTE dentro de cualquier zona
class ClasificacionTree
{
    private float $pesoMaximoPequeno;
    private float $pesoMaximoMediano;

    public function __construct()
    {
        $config = Configuracion::first();
        $this->pesoMaximoPequeno = $config ? (float)$config->peso_maximo_pequeno : 4.0;
        $this->pesoMaximoMediano = $config ? (float)$config->peso_maximo_mediano : 20.0;
    }

    // Retorna la categoría: 'pequeño', 'mediano', 'grande'
    public function clasificar(float $peso, string $dimensiones = ''): string
    {
        if ($peso <= $this->pesoMaximoPequeno) {
            return 'pequeño';
        } elseif ($peso <= $this->pesoMaximoMediano) {
            return 'mediano';
        } else {
            return 'grande';
        }
    }

    // Retorna el estante dentro de la zona
    // Estante 1 = arriba (pequeños), Estante 2 = medio, Estante 3 = abajo (grandes)
    public function asignarEstante(float $peso, string $dimensiones = ''): int
    {
        $categoria = $this->clasificar($peso, $dimensiones);
        switch ($categoria) {
            case 'pequeño': return 1;
            case 'mediano': return 2;
            case 'grande':  return 3;
            default:        return 1;
        }
    }
}