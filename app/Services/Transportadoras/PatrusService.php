<?php
namespace App\Services\Transportadoras;

use App\Models\Nfe;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PatrusService
{
    protected string $baseUrl = "https://api-patrus.azure-api.net/api/v1/logistica/crm/trackings";

    public function consultar(Nfe $nf): array
    {
        $token        = app(PatrusAuthService::class)->getToken();
        $subscription = config('transportadoras.patrus.subscription');

        try {
            $response = Http::withHeaders([
                "Authorization"             => "Bearer {$token}",
                "Ocp-Apim-Subscription-Key" => $subscription,
                "Accept"                    => "application/json",
            ])->get($this->baseUrl, [
                "filtros.notaFiscal"      => $nf->numero,
                "filtros.serieNotaFiscal" => $nf->serie,
            ]);

            if ($response->failed()) {
                return [
                    "status"      => "http_error",
                    "mensagem"    => "Erro HTTP Patrus: ".$response->status(),
                    "data_evento" => now()
                ];
            }

            $json = $response->json();

            if (!is_array($json) || count($json) === 0) {
                return [
                    "status"      => "not_found",
                    "mensagem"    => "Nenhum registro encontrado na Patrus",
                    "data_evento" => now(),
                ];
            }

            $item = $json[0];

            $last = $item["Eventos"][0] ?? null;

            $descricao = $last["Descricao"] ?? "Evento não informado";
            $data      = $last["Data"] ?? now();

            return [
                "status"      => $this->mapStatus($descricao),
                "mensagem"    => $descricao,
                "data_evento" => \Carbon\Carbon::parse($data),
            ];

        } catch (\Throwable $e) {
            Log::error("❌ PatrusService exception: ".$e->getMessage());

            return [
                "status"      => "http_error",
                "mensagem"    => substr($e->getMessage(), 0, 200),
                "data_evento" => now(),
            ];
        }
    }

    private function mapStatus(string $desc): string
    {
        $d = strtolower($desc);

        return match (true) {
            str_contains($d, 'entreg')           => 'delivered',
            str_contains($d, 'colet')            => 'in_transit',
            str_contains($d, 'trânsito'),
            str_contains($d, 'transito')         => 'in_transit',
            str_contains($d, 'saiu para entrega') => 'in_transit',
            str_contains($d, 'não localizado')   => 'not_found',
            default                               => 'in_transit',
        };
    }
}
