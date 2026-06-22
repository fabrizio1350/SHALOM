<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Alerta;
use App\Models\Encomienda;
use App\DataStructures\AlertQueue;

class AlertaController extends Controller
{
    // Listar alertas activas usando función PL/pgSQL con cursor
    public function index()
    {
        $alertasRaw = DB::select('SELECT * FROM obtener_alertas_activas()');

        $queue = new AlertQueue();
        foreach ($alertasRaw as $alerta) {
            $queue->enqueue((array)$alerta);
        }

        $total_en_cola = $queue->size();

        $alertas = Alerta::with('encomienda')
                    ->where('estado', '!=', 'resuelta')
                    ->orderBy('fecha_generada', 'asc')
                    ->get();

        return view('alertas.index', compact('alertas', 'total_en_cola'));
    }

    // Atender alerta
    public function atender(Request $request, $id)
    {
        $request->validate([
            'accion'      => 'required|in:atendida,resuelta',
            'observacion' => 'nullable|string'
        ]);

        $alerta = Alerta::findOrFail($id);
        $alerta->estado = $request->accion;
        $alerta->save();

        if ($request->accion === 'resuelta') {
            DB::statement('CALL cambiar_estado_encomienda(?, ?, ?, ?)', [
                $alerta->id_encomienda,
                'en_espera',
                'Alerta resuelta: ' . $request->observacion,
                Auth::id()
            ]);

            // Enviar notificación al operario por Telegram
            $this->notificarTelegram($alerta->id_encomienda, $request->observacion);
        }

        return redirect()->route('alertas.index')->with('success', 'Alerta actualizada.');
    }

    // Enviar mensaje a Telegram
    private function notificarTelegram(string $idEncomienda, ?string $observacion): void
    {
        $token  = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        $mensaje = "⚠️ *ALERTA RESUELTA - Shalom*\n\n"
                 . "📦 *Encomienda:* `{$idEncomienda}`\n"
                 . "📝 *Acción requerida:* Por favor reubicar el paquete\n"
                 . "💬 *Observación:* " . ($observacion ?? 'Sin observación') . "\n"
                 . "📅 *Fecha:* " . now()->format('d/m/Y H:i') . "\n\n"
                 . "🏭 _Sistema de Gestión Shalom_";

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id'    => $chatId,
            'text'       => $mensaje,
            'parse_mode' => 'Markdown'
        ]);
    }
}