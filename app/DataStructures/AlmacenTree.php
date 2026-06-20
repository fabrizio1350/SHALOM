<?php

namespace App\DataStructures;

// Árbol de Almacén — Zona → Estante → Paquete
// Árbol no degenerado con 3 niveles y múltiples ramas
class AlmacenTree
{
    private array $root = [];

    public function __construct()
    {
        $this->root = [
            'nombre' => 'Almacén Shalom',
            'tipo'   => 'almacen',
            'hijos'  => []
        ];
    }

    public function agregarZona(array $zona): void
    {
        $this->root['hijos'][$zona['id']] = [
            'id'     => $zona['id'],
            'nombre' => $zona['nombre'],
            'estado' => $zona['estado'],
            'tipo'   => 'zona',
            'hijos'  => [
                1 => ['estante' => 1, 'nombre' => 'Estante 1 (Arriba - Pequeños)', 'tipo' => 'estante', 'paquetes' => []],
                2 => ['estante' => 2, 'nombre' => 'Estante 2 (Medio - Medianos)',  'tipo' => 'estante', 'paquetes' => []],
                3 => ['estante' => 3, 'nombre' => 'Estante 3 (Abajo - Grandes)',   'tipo' => 'estante', 'paquetes' => []],
            ]
        ];
    }

    public function agregarPaquete(array $encomienda, int $estante): void
    {
        $idZona = $encomienda['id_zona'];
        if (!isset($this->root['hijos'][$idZona])) return;
        $this->root['hijos'][$idZona]['hijos'][$estante]['paquetes'][] = $encomienda;
    }

    public function getTree(): array
    {
        return $this->root;
    }

    public function getZonas(): array
    {
        return $this->root['hijos'];
    }

    public function contarPaquetesEnZona(int $idZona): int
    {
        if (!isset($this->root['hijos'][$idZona])) return 0;
        $total = 0;
        foreach ($this->root['hijos'][$idZona]['hijos'] as $estante) {
            $total += count($estante['paquetes']);
        }
        return $total;
    }
}