<?php

namespace App\DataStructures;

// Pila LIFO — Lab S08 de Algoritmos
// El historial muestra el cambio más reciente primero
class HistorialStack
{
    private array $stack = [];

    public function push(array $movimiento): void
    {
        array_push($this->stack, $movimiento);
    }

    public function pop(): ?array
    {
        return array_pop($this->stack); // LIFO: saca el último
    }

    public function peek(): ?array
    {
        return end($this->stack) ?: null; // Ver el último sin sacarlo
    }

    public function isEmpty(): bool
    {
        return empty($this->stack);
    }

    public function size(): int
    {
        return count($this->stack);
    }

    public function toArray(): array
    {
        return array_reverse($this->stack); // Más reciente primero
    }
}