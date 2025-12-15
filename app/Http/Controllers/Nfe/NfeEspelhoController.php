<?php

namespace App\Http\Controllers\Nfe;

use App\Models\Nfe;
use App\Http\Controllers\Controller;
use Picqer\Barcode\BarcodeGeneratorPNG;

class NfeEspelhoController extends Controller
{
    /**
     * Formata todos os dados necessários para o espelho da NFe
     */
    public function formatarDados(Nfe $nfe): array
    {
        // ===============================
        // BARCODE (Code128 como imagem)
        // ===============================
        $generator = new BarcodeGeneratorPNG();

        $barcodeBase64 = base64_encode(
            $generator->getBarcode(
                $nfe->chave,
                $generator::TYPE_CODE_128
            )
        );

        // ===============================
        // LOGO (base64)
        // ===============================
        $logoPath = public_path('images/pferd-tools.png');

        $logoBase64 = file_exists($logoPath)
            ? base64_encode(file_get_contents($logoPath))
            : null;

        // ===============================
        // DADOS DA NFE
        // ===============================
        return [
            'chave' => $nfe->chave,
            'numero' => $nfe->numero,
            'serie' => $nfe->serie,
            'data_emissao' => optional($nfe->data_emissao)->format('d/m/Y H:i'),
            'natureza_operacao' => $nfe->natureza_operacao,
            'tipo_operacao' => $nfe->tipo_operacao,
            'protocolo_autorizacao' => $nfe->protocolo_autorizacao,
            'data_autorizacao' => optional($nfe->data_autorizacao)->format('d/m/Y H:i'),
            'status_autorizacao' => $nfe->status_autorizacao,
            'motivo_autorizacao' => $nfe->motivo_autorizacao,
            'crt' => $nfe->crt,

            // ===============================
            // EMITENTE
            // ===============================
            'emitente' => [
                'nome' => $nfe->emitente_nome,
                'cnpj' => $nfe->emitente_cnpj,
                'ie' => $nfe->emitente_ie,
                'endereco' => $nfe->emitente_endereco,
                'municipio' => $nfe->emitente_municipio,
                'uf' => $nfe->emitente_uf,
                'cep' => $nfe->emitente_cep,
            ],

            // ===============================
            // DESTINATÁRIO
            // ===============================
            'destinatario' => [
                'nome' => $nfe->dest_nome,
                'cnpj' => $nfe->dest_cnpj,
                'ie' => $nfe->dest_ie,
                'endereco' => $nfe->dest_endereco,
                'municipio' => $nfe->dest_municipio,
                'uf' => $nfe->dest_uf,
                'cep' => $nfe->dest_cep,
            ],

            // ===============================
            // TOTAIS
            // ===============================
            'totais' => [
                'valor_total' => $nfe->valor_total,
                'valor_produtos' => $nfe->valor_produtos,
                'valor_frete' => $nfe->valor_frete,
                'valor_seguro' => $nfe->valor_seguro,
                'valor_desconto' => $nfe->valor_desconto,
                'valor_tributos' => $nfe->valor_tributos,
            ],

            // ===============================
            // TRANSPORTE
            // ===============================
            'transporte' => [
                'mod_frete' => $nfe->mod_frete,
                'nome' => $nfe->transportadora_nome,
                'cnpj' => $nfe->transportadora_cnpj,
                'quantidade' => $nfe->volume_quantidade,
                'especie' => $nfe->volume_especie,
                'peso_bruto' => $nfe->peso_bruto,
                'peso_liquido' => $nfe->peso_liquido,
            ],

            // ===============================
            // FATURA
            // ===============================
            'fatura' => [
                'numero' => $nfe->fatura_numero,
                'valor' => $nfe->fatura_valor,
                'vencimento' => optional($nfe->data_vencimento)?->format('d/m/Y'),
            ],

            // ===============================
            // ITENS
            // ===============================
            'itens' => $nfe->itens->map(function ($i) {
                return [
                    'codigo' => $i->codigo_produto,
                    'descricao' => $i->descricao,
                    'ncm' => $i->ncm,
                    'cfop' => $i->cfop,
                    'unidade' => $i->unidade,
                    'quantidade' => $i->quantidade,
                    'valor_unitario' => $i->valor_unitario,
                    'valor_total' => $i->valor_total,

                    'icms' => [
                        'cst' => $i->icms->cst ?? null,
                        'v_bc' => $i->icms->v_bc ?? null,
                        'v_icms' => $i->icms->v_icms ?? null,
                        'p_icms' => $i->icms->p_icms ?? null,
                    ],

                    'ipi' => [
                        'cst' => $i->ipi->cst ?? null,
                        'v_ipi' => $i->ipi->v_ipi ?? null,
                        'p_ipi' => $i->ipi->p_ipi ?? null,
                    ],
                ];
            })->toArray(),

            // ===============================
            // OUTROS
            // ===============================
            'informacoes' => $nfe->informacoes_adicionais,

            // ===============================
            // IMAGENS
            // ===============================
            'barcode_base64' => $barcodeBase64,
            'logo_base64' => $logoBase64,
        ];
    }
}
