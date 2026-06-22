<?php

namespace App\DataStructures;

// Nodo del árbol binario de búsqueda
class NodoBST
{
    public string $nombre;
    public string $id_encomienda;
    public array  $datos; // datos completos de la encomienda
    public ?NodoBST $izquierdo = null;
    public ?NodoBST $derecho   = null;

    public function __construct(string $nombre, string $id_encomienda, array $datos)
    {
        $this->nombre        = $nombre;
        $this->id_encomienda = $id_encomienda;
        $this->datos         = $datos;
    }
}

// Árbol Binario de Búsqueda — BST
// Ordenado alfabéticamente por nombre del remitente
// Búsqueda eficiente O(log n) vs O(n) de lista lineal
class BinarySearchTree
{
    private ?NodoBST $raiz = null;
    private int      $totalNodos = 0;

    // Insertar encomienda en el árbol
    public function insertar(string $nombre, string $id_encomienda, array $datos): void
    {
        $this->raiz = $this->insertarNodo($this->raiz, $nombre, $id_encomienda, $datos);
        $this->totalNodos++;
    }

    private function insertarNodo(?NodoBST $nodo, string $nombre, string $id_encomienda, array $datos): NodoBST
    {
        // Si el nodo es null, crear nuevo nodo aquí
        if ($nodo === null) {
            return new NodoBST($nombre, $id_encomienda, $datos);
        }

        // Comparar alfabéticamente
        $comparacion = strcasecmp($nombre, $nodo->nombre);

        if ($comparacion < 0) {
            // Nombre menor → va a la izquierda
            $nodo->izquierdo = $this->insertarNodo($nodo->izquierdo, $nombre, $id_encomienda, $datos);
        } else {
            // Nombre mayor o igual → va a la derecha
            $nodo->derecho = $this->insertarNodo($nodo->derecho, $nombre, $id_encomienda, $datos);
        }

        return $nodo;
    }

    // Buscar encomiendas por nombre (búsqueda parcial)
    public function buscar(string $termino): array
    {
        $resultados = [];
        $this->buscarNodo($this->raiz, strtolower($termino), $resultados);
        return $resultados;
    }

    private function buscarNodo(?NodoBST $nodo, string $termino, array &$resultados): void
    {
        if ($nodo === null) return;

        $nombreNodo = strtolower($nodo->nombre);

        // Verificar si el nombre contiene el término buscado
        if (str_contains($nombreNodo, $termino)) {
            $resultados[] = $nodo->datos;
        }

        // Recorrer ambos subárboles para búsqueda parcial
        $this->buscarNodo($nodo->izquierdo, $termino, $resultados);
        $this->buscarNodo($nodo->derecho, $termino, $resultados);
    }

    // Recorrido inorden (izquierda → raíz → derecha) = orden alfabético
    public function inorden(): array
    {
        $resultado = [];
        $this->inordenNodo($this->raiz, $resultado);
        return $resultado;
    }

    private function inordenNodo(?NodoBST $nodo, array &$resultado): void
    {
        if ($nodo === null) return;
        $this->inordenNodo($nodo->izquierdo, $resultado); // izquierda primero
        $resultado[] = $nodo->datos;                       // raíz
        $this->inordenNodo($nodo->derecho, $resultado);    // derecha al final
    }

    // Buscar nodo exacto por código SHL
    public function buscarPorCodigo(string $codigo): ?array
    {
        return $this->buscarPorCodigoNodo($this->raiz, $codigo);
    }

    private function buscarPorCodigoNodo(?NodoBST $nodo, string $codigo): ?array
    {
        if ($nodo === null) return null;

        if ($nodo->id_encomienda === $codigo) return $nodo->datos;

        $resultado = $this->buscarPorCodigoNodo($nodo->izquierdo, $codigo);
        if ($resultado !== null) return $resultado;

        return $this->buscarPorCodigoNodo($nodo->derecho, $codigo);
    }

    // Total de nodos en el árbol
    public function totalNodos(): int
    {
        return $this->totalNodos;
    }

    // Retorna la raíz del árbol
    public function getRaiz(): ?NodoBST
    {
        return $this->raiz;
    }
}