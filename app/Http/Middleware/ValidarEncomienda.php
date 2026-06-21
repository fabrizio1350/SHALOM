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

                    if ($largo > 120) {
                        return back()->withErrors(['dimensiones' => 'El largo máximo es 120 cm.'])->withInput();
                    }
                    if ($ancho > 80) {
                        return back()->withErrors(['dimensiones' => 'El ancho máximo es 80 cm.'])->withInput();
                    }
                    if ($alto > 80) {
                        return back()->withErrors(['dimensiones' => 'El alto máximo es 80 cm.'])->withInput();
                    }

                    $volumen = $largo * $ancho * $alto;
                    if ($volumen > 768000) {
                        return back()->withErrors(['dimensiones' => 'Las dimensiones superan el volumen máximo permitido (120x80x80 cm).'])->withInput();
                    }
                }
            }
        }

        return $next($request);
    }
}