<?php

namespace App\Http\Controllers\ReturnProcess;


use App\Http\Controllers\AjaxController;
use Illuminate\Http\JsonResponse;

class ReturnProcessItemController extends AjaxController
{
    public function index($id): JsonResponse
    {
        return $this->ajaxResponse(true, 'Itens carregados com sucesso!');
    }
}
