<?php

namespace App\Http\Controllers\ReturnProcess;

use App\Http\Controllers\AjaxController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReturnProcessStepController extends AjaxController
{
    public function update($id, Request $request): JsonResponse
    {
        return $this->ajaxResponse(true, 'Etapa atualizada com sucesso!');
    }
}
