<?php

namespace App\DataStructures;

// Pila LIFO con estructura de árbol — Lab S08 de Algoritmos
// El historial se guarda como árbol donde cada nodo apunta a su padre
class HistorialStack
{
    private array $stack = [];
    private array $tree  = [];

    public function push(array $movimiento): void
    {
        array_push($this->stack, $movimiento);

        // Construir árbol con id_padre
        $id = $movimiento['id'] ?? count($this->stack);
        $this->tree[$id] = [
            'data'   => $movimiento,
            'padre'  => $movimiento['id_padre'] ?? null,
            'hijos'  => []
        ];

        // Registrar como hijo del padre
        $idPadre = $movimiento['id_padre'] ?? null;
        if ($idPadre && isset($this->tree[$idPadre])) {
            $this->tree[$idPadre]['hijos'][] = $id;
        }
    }

    public function pop(): ?array
    {
        return array_pop($this->stack); // LIFO
    }

    public function peek(): ?array
    {
        return end($this->stack) ?: null;
    }

    public function isEmpty(): bool
    {
        return empty($this->stack);
    }

    public function size(): int
    {
        return count($this->stack);
    }

    // Retorna historial ordenado más reciente primero (LIFO)
    public function toArray(): array
    {
        return array_reverse($this->stack);
    }

    // Retorna el árbol completo de movimientos
    public function getTree(): array
    {
        return $this->tree;
    }

    // Retorna la raíz del árbol (primer movimiento)
    public function getRoot(): ?array
    {
        foreach ($this->tree as $nodo) {
            if ($nodo['padre'] === null) {
                return $nodo;
            }
        }
        return null;
    }
}