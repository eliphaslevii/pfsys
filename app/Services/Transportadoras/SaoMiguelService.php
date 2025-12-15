<?php

namespace App\Services\Transportadoras;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Nfe;
use Carbon\Carbon;

class SaoMiguelService
{
    protected string $url;
    protected string $accessKey;
    protected string $customerCnpj;

    public function __construct()
    {
        $this->url = rtrim(config('transportadoras.sao_miguel.base_url'), '/') . '/tracking';

        $this->accessKey = config('transportadoras.sao_miguel.access_key');
        $this->customerCnpj = config('transportadoras.sao_miguel.customer_cnpj');
    }

    public function consultar(Nfe $nf): array
    {
        try {
            $payload = [
                'valoresParametros' => [
                    $this->customerCnpj,             // <<< CORRETO
                    (int) $nf->numero,
                    (int) ($nf->serie ?? 1)
                ]
            ];

            $response = Http::timeout(20)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Access_Key' => $this->accessKey,
                    'Customer' => $this->customerCnpj,
                    'Modelo_Consulta' => 'TRACKING_COMPLETO_POR_NOTA_FISCAL_E_COMPROVANTE'
                ])
                ->post($this->url, $payload);

            if (!$response->successful()) {
                Log::warning("São Miguel API HTTP Error", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'nf' => $nf->numero
                ]);

                return [
                    'status' => 'http_error',
                    'message' => "Erro HTTP ({$response->status()})"
                ];
            }

            $data = $response->json();

            if (empty($data) || !isset($data[0])) {
                return [
                    'status' => 'not_found',
                    'message' => 'Nenhuma informação encontrada'
                ];
            }

            $info = $data[0];

            if (!empty($info['dadosEntrega']['dataEntrega'])) {
                $dataEntrega = $this->parseDate($info['dadosEntrega']['dataEntrega']);

                return [
                    'status' => 'delivered',
                    'data_entrega' => $dataEntrega,
                    'message' => 'ENTREGUE'
                ];
            }

            if (!empty($info['ocorrencias'])) {
                $ultima = end($info['ocorrencias']);

                $desc = strtoupper($ultima['descricaoOcorrencia'] ?? 'STATUS DESCONHECIDO');
                $dataRegistro = $ultima['dataRegistro'] ?? null;

                if (str_contains($desc, 'ENTREG') || str_contains($desc, 'REALIZADA')) {
                    return [
                        'status' => 'delivered',
                        'data_entrega' => $this->parseDate($dataRegistro),
                        'message' => $desc
                    ];
                }

                return [
                    'status' => 'in_transit',
                    'message' => $desc
                ];
            }

            return [
                'status' => 'in_transit',
                'message' => 'Sem ocorrências registradas'
            ];

        } catch (\Throwable $e) {

            Log::error("São Miguel Exception [NF {$nf->numero}]: " . $e->getMessage());

            return [
                'status' => 'exception',
                'message' => 'Erro interno ao consultar São Miguel'
            ];
        }
    }

    private function parseDate($value): ?string
    {
        if (!$value)
            return null;

        try {
            return Carbon::parse(str_replace('/', '-', $value))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning("Erro ao converter data da SM: " . $value);
            return null;
        }
    }
}
