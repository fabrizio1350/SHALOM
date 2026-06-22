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

            // Validar dimensiones
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

            // Validar imagen si se subió
            if ($request->hasFile('imagen')) {
                $imagen    = $request->file('imagen');
                $extension = strtolower($imagen->getClientOriginalExtension());

                if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    return back()->withErrors(['imagen' => 'Solo se permiten imágenes JPG o PNG.'])->withInput();
                }

                if ($imagen->getSize() > 2 * 1024 * 1024) {
                    return back()->withErrors(['imagen' => 'La imagen no puede superar 2MB.'])->withInput();
                }
            }
        }

        return $next($request);
    }
}