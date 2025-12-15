<?php

namespace App\Services\Transportadoras;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Nfe;

class AlfaService
{
    protected string $url;
    protected string $token;
    protected string $customer;

    public function __construct()
    {
        $this->url = 'https://api.alfatransportes.com.br/rastreamento/v1.3/';
        $this->token = config('transportadoras.alfa.token');
        $this->customer = config('transportadoras.alfa.customer_cnpj');
    }

    public function consultar(Nfe $nf): array
    {
        try {
            $payload = [
                "idr" => $this->token,
                "merNF" => (string)$nf->numero,
                "cnpjTomador" => $this->customer
            ];

            $response = Http::acceptJson()->post($this->url, $payload);

            if (!$response->successful()) {
                return [
                    'status' => 'http_error',
                    'mensagem' => "HTTP {$response->status()}",
                    'data_evento' => null
                ];
            }

            $json = $response->json();

            if (is_array($json)) {
                return $this->padronizarJson($json);
            }

            return [
                'status' => 'not_found',
                'mensagem' => 'Retorno inválido',
                'data_evento' => null
            ];

        } catch (\Throwable $e) {
            Log::error("AlfaService ERROR: ".$e->getMessage());

            return [
                'status' => 'exception',
                'mensagem' => $e->getMessage(),
                'data_evento' => null
            ];
        }
    }

    private function padronizarJson(array $data): array
    {
        if (!isset($data['status'])) {
            return [
                'status' => 'not_found',
                'mensagem' => 'Sem status',
                'data_evento' => null
            ];
        }

        $code = (int)$data['status'];
        $msg  = $data['nome'] ?? 'Sem informação';

        // 2 = entregue
        if ($code === 2) {
            $entrega = $data['dadosEntrega']['dataEntrega'] ?? null;

            return [
                'status' => 'delivered',
                'mensagem' => $msg,
                'data_evento' => $entrega
            ];
        }

        // 1 = em trânsito
        if ($code === 1) {
            $oc = $data['ocorrenciasExtras'] ?? [];
            $msgFinal = $msg;

            if (!empty($oc)) {
                $last = end($oc);
                $msgFinal = $last['descricaoOcorrencia'] ?? $msg;
            }

            return [
                'status' => 'in_transit',
                'mensagem' => $msgFinal,
                'data_evento' => null
            ];
        }

        // 9 = nota não encontrada
        if ($code === 9) {
            return [
                'status' => 'not_found',
                'mensagem' => 'Nota fiscal não encontrada neste CNPJ.',
                'data_evento' => null
            ];
        }

        return [
            'status' => 'not_found',
            'mensagem' => $msg,
            'data_evento' => null
        ];
    }
}
