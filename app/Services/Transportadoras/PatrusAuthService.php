<?php

namespace App\Services\Transportadoras;

use App\Models\Nfe;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PatrusAuthService
{
    public function getToken(): string
    {
        return Cache::remember('patrus_token', 3500, function () {

            $response = Http::asForm()->post(config('transportadoras.patrus.oauth_url'), [
                'grant_type'    => 'password',
                'username'      => config('transportadoras.patrus.username'),
                'password'      => config('transportadoras.patrus.password'),
                'client_id'     => config('transportadoras.patrus.client_id'),
                'client_secret' => config('transportadoras.patrus.client_secret'),
            ]);

            if ($response->failed()) {
                Log::error("❌ Falha ao gerar token Patrus: ".$response->body());
                throw new \Exception("Erro ao gerar token Patrus");
            }

            return $response->json()['access_token'];
        });
    }
}
