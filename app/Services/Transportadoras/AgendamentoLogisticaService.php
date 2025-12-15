<?php

namespace App\Services\Transportadoras;

use App\Models\AgendamentoLogistica;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Nfe;

class AgendamentoLogisticaService
{
    public function criarOuSugerirParaNfe(Nfe $nfe): AgendamentoLogistica
    {
        // normaliza nome da transportadora
        $transp = strtoupper($nfe->transportadora_nome ?? 'DESCONHECIDA');

        // tenta achar agendamento pendente da mesma transportadora
        $agendamento = AgendamentoLogistica::where('transportadora_nome', $transp)
            ->where('status', 'pendente')
            ->first();

        if (!$agendamento) {
            $agendamento = AgendamentoLogistica::create([
                'transportadora_nome' => $transp,
                'transportadora_cnpj' => $nfe->transportadora_cnpj,
                'status' => 'pendente'
            ]);
        }

        // vincula NF ao agendamento
        $agendamento->nfes()->syncWithoutDetaching([$nfe->id]);

        return $agendamento;
    }
}

