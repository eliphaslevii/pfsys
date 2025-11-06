<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AjaxController extends Controller
{
    /**
     * Resposta JSON padrão para sucesso ou erro.
     */
    protected function ajaxResponse(bool $success, string $message, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
