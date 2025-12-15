<?php

namespace App\Services\Nfe;

use App\Models\Nfe;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Http\Controllers\Nfe\NfeEspelhoController;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;

class EspelhoNfeService
{
    public function gerarLotePdf(array $nfeIds): string
    {
        $zipName = 'espelhos_' . now()->format('Ymd_His') . '.zip';
        $tempZip = tempnam(sys_get_temp_dir(), 'zip_');

        $zip = new ZipArchive();
        $zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($nfeIds as $id) {
            $nfe = Nfe::with([
                'itens.icms',
                'itens.ipi',
                'itens.pis',
                'itens.cofins',
                'itens.ibscbs',
                'ibscbsTot'
            ])->findOrFail($id);

            $dados = app(NfeEspelhoController::class)->formatarDados($nfe);
            $pdf = $this->gerarPdfStream($dados);

            $zip->addFromString("NFe_{$nfe->chave}.pdf", $pdf);
        }

        $zip->close();

        Storage::disk('public')->put(
            "espelhos/$zipName",
            file_get_contents($tempZip)
        );

        unlink($tempZip);

        return "espelhos/$zipName";
    }

    protected function gerarPdfStream(array $dados): string
    {
        return Pdf::loadView('nfe.espelho', [
            'nfe' => $dados
        ])
            ->setPaper('A4')
            ->output();
    }
}
