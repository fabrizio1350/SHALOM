<?php

namespace App\DataStructures;

// Cola FIFO — Lab S07 de Algoritmos
// Las alertas se procesan en orden de llegada
class AlertQueue
{
    private array $queue = [];

    public function enqueue(array $alerta): void
    {
        array_push($this->queue, $alerta);
    }

    public function dequeue(): ?array
    {
        return array_shift($this->queue); // FIFO: saca el primero
    }

    public function isEmpty(): bool
    {
        return empty($this->queue);
    }

    public function size(): int
    {
        return count($this->queue);
    }

    public function toArray(): array
    {
        return $this->queue;
    }
}