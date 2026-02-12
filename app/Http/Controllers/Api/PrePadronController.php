<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrePadron;
use Illuminate\Http\Request;

class PrePadronController extends Controller
{
    public function buscarPorCedula($cedula)
    {
        $persona = PrePadron::where('cedula', $cedula)->first();

        if (!$persona) {
            return response()->json([
                'success' => false,
                'message' => 'No encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $persona
        ]);
    }
}
