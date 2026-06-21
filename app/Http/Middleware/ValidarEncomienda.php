<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidarEncomienda
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') && $request->route()->getName() === 'encomiendas.registrar') {
            
            if ($request->dimensiones) {
                $dims = explode('x', strtolower(str_replace(' ', '', $request->dimensiones)));
                
                if (count($dims) === 3) {
                    $largo = (int)$dims[0];
                    $ancho = (int)$dims[1];
                    $alto  = (int)$dims[2];

                    if ($largo > 600) {
                        return back()->withErrors(['dimensiones' => 'El largo máximo es 600 cm.'])->withInput();
                    }
                    if ($ancho > 230) {
                        return back()->withErrors(['dimensiones' => 'El ancho máximo es 230 cm.'])->withInput();
                    }
                    if ($alto > 240) {
                        return back()->withErrors(['dimensiones' => 'El alto máximo es 240 cm.'])->withInput();
                    }

                    $volumen = ($largo * $ancho * $alto) / 1000000;
                    if ($volumen > 12.7) {
                        return back()->withErrors(['dimensiones' => 'El volumen máximo es 12.7 m³. Requiere cotización especial.'])->withInput();
                    }
                }
            }
        }

        return $next($request);
    }
}