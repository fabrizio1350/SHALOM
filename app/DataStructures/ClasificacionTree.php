<?php

namespace App\DataStructures;

// Árbol de decisión — Labs S09-S12 de Algoritmos
// Clasifica paquetes por peso y tamaño para asignar zona y estante
class ClasificacionTree
{
    // Retorna la categoría: 'pequeño', 'mediano', 'grande'
    public function clasificar(float $peso, string $dimensiones = ''): string
    {
        // Árbol de decisión por peso primero
        if ($peso <= 5) {
            if ($dimensiones) {
                $volumen = $this->calcularVolumen($dimensiones);
                if ($volumen <= 5000) return 'pequeño';
                return 'mediano';
            }
            return 'pequeño';
        } elseif ($peso <= 20) {
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