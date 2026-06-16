<?php

namespace App\DataStructures;

use App\Models\Configuracion;

// Árbol de decisión — Labs S09-S12 de Algoritmos
// Clasifica paquetes por peso y tamaño para asignar zona y estante
// Los criterios de peso se leen de la BD (configurados por el admin)
class ClasificacionTree
{
    private float $pesoMaximoPequeno;
    private float $pesoMaximoMediano;

    public function __construct()
    {
        // Leer criterios de la BD
        $config = Configuracion::first();
        $this->pesoMaximoPequeno = $config ? (float)$config->peso_maximo_pequeno : 5.0;
        $this->pesoMaximoMediano = $config ? (float)$config->peso_maximo_mediano : 20.0;
    }

    // Retorna la categoría: 'pequeño', 'mediano', 'grande'
    public function clasificar(float $peso, string $dimensiones = ''): string
    {
        if ($peso <= $this->pesoMaximoPequeno) {
            if ($dimensiones) {
                $volumen = $this->calcularVolumen($dimensiones);
                if ($volumen <= 5000) return 'pequeño';
                return 'mediano';
            }
            return 'pequeño';
        } elseif ($peso <= $this->pesoMaximoMediano) {
            if ($dimensiones) {
                $volumen = $this->calcularVolumen($dimensiones);
                if ($volumen <= 15000) return 'mediano';
                return 'grande';
            }
            return 'mediano';
        } else {
            return 'grande';
        }
    }

    // Retorna el estante según la categoría
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

    // Calcular volumen desde dimensiones "LxAxH"
    private function calcularVolumen(string $dimensiones): float
    {
        $dims = explode('x', strtolower(str_replace(' ', '', $dimensiones)));
        if (count($dims) === 3) {
            return (float)$dims[0] * (float)$dims[1] * (float)$dims[2];
        }
        return 0;
    }
}