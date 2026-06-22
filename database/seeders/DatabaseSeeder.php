<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Zona;
use App\Models\Configuracion;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Primero los usuarios
        User::insert([
            ['name' => 'Administrador', 'email' => 'admin@shalom.com', 'password' => bcrypt('admin123'), 'rol' => 'administrador', 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Operario', 'email' => 'operario@shalom.com', 'password' => bcrypt('operario123'), 'rol' => 'operario', 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supervisor', 'email' => 'supervisor@shalom.com', 'password' => bcrypt('supervisor123'), 'rol' => 'supervisor', 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Zonas del almacen
        Zona::insert([
            ['nombre' => 'Zona A', 'capacidad' => 20, 'estado' => 'disponible', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Zona B', 'capacidad' => 15, 'estado' => 'disponible', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Zona C', 'capacidad' => 10, 'estado' => 'disponible', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Configuracion inicial
        Configuracion::create([
            'tiempo_maximo_dias'  => 7,
            'peso_maximo_pequeno' => 5.0,
            'peso_maximo_mediano' => 20.0,
            'id_zona_reubicacion' => 1,
            'fecha_actualizacion' => now(),
            'id_admin'            => 1
        ]);

        // 4. Encomiendas de prueba via procedimiento
        $encomiendas = [
            ['Juan Perez',   'Maria Garcia',  'Lima',     2.5,  '20x15x10', 'Ropa'],
            ['Carlos Lopez', 'Ana Torres',    'Cusco',    8.0,  '30x25x20', 'Libros'],
            ['Luis Mamani',  'Rosa Quispe',   'Arequipa', 25.0, '50x40x30', 'Electrodomestico'],
            ['Pedro Flores', 'Carmen Huanca', 'Puno',     1.2,  '15x10x8',  'Documentos'],
            ['Jose Condori', 'Elena Vargas',  'Tacna',    15.0, '40x30x25', 'Herramientas'],
        ];

        foreach ($encomiendas as $enc) {
            DB::statement('CALL registrar_encomienda(?, ?, ?, ?, ?, ?, ?)', [
                $enc[0], $enc[1], $enc[2], $enc[3], $enc[4], $enc[5], 1
            ]);
        }
    }
}