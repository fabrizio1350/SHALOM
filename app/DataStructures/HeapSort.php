<?php

namespace App\DataStructures;

// Algoritmo HeapSort — Ordenamiento por Montículo
// Complejidad: O(n log n) en todos los casos
// Usado para ordenar encomiendas por peso en reportes
class HeapSort
{
    private array $data = [];
    private int   $n    = 0;

    public function __construct(array $items)
    {
        $this->data = array_values($items);
        $this->n    = count($this->data);
    }

    // Ordena el array usando HeapSort
    public function sort(string $campo = 'peso', string $orden = 'desc'): array
    {
        $this->buildMaxHeap($campo);

        for ($i = $this->n - 1; $i > 0; $i--) {
            // Mover raíz (máximo) al final
            $this->swap(0, $i);
            // Reconstruir heap en el subarray reducido
            $this->heapify($i, 0, $campo);
        }

        if ($orden === 'desc') {
            return array_reverse($this->data);
        }

        return $this->data;
    }

    // Construir el Max-Heap inicial
    private function buildMaxHeap(string $campo): void
    {
        // Empezar desde el último nodo no hoja
        for ($i = (int)floor($this->n / 2) - 1; $i >= 0; $i--) {
            $this->heapify($this->n, $i, $campo);
        }
    }

    // Mantener la propiedad del heap para un subárbol
    private function heapify(int $tamano, int $i, string $campo): void
    {
        $mayor     = $i;
        $izquierdo = 2 * $i + 1; // hijo izquierdo
        $derecho   = 2 * $i + 2; // hijo derecho

        // ¿El hijo izquierdo es mayor que la raíz?
        if ($izquierdo < $tamano &&
            $this->valor($izquierdo, $campo) > $this->valor($mayor, $campo)) {
            $mayor = $izquierdo;
        }

        // ¿El hijo derecho es mayor que el actual mayor?
        if ($derecho < $tamano &&
            $this->valor($derecho, $campo) > $this->valor($mayor, $campo)) {
            $mayor = $derecho;
        }

        // Si el mayor no es la raíz, intercambiar y continuar
        if ($mayor !== $i) {
            $this->swap($i, $mayor);
            $this->heapify($tamano, $mayor, $campo);
        }
    }

    // Obtener valor del campo para comparar
    private function valor(int $index, string $campo): float
    {
        $item = $this->data[$index];
        if (is_array($item)) {
            return (float)($item[$campo] ?? 0);
        }
        return (float)($item->$campo ?? 0);
    }

    // Intercambiar dos elementos
    private function swap(int $i, int $j): void
    {
        [$this->data[$i], $this->data[$j]] = [$this->data[$j], $this->data[$i]];
    }

    // Retorna el tamaño del heap
    public function size(): int
    {
        return $this->n;
    }
}