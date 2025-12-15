<?php

namespace App\Http\Controllers\Logistic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nfe;

class EntregaController extends Controller
{
    // No seu Controller
    // App/Http/Controllers/Logistic/EntregaController.php

    public function index(Request $request)
    {
        $query = Nfe::query()->with('lastTracking');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('numero', 'LIKE', "%{$term}%")
                    ->orWhere('dest_nome', 'LIKE', "%{$term}%")
                    ->orWhere('dest_cnpj', 'LIKE', "%{$term}%");
            });
        }

        $nfes = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // ============================
        // Regras visuais aqui!
        // ============================

        $integradas = ['SAO MIGUEL', 'ALFA', 'PATRUS'];

        $statusMap = [
            'delivered'      => ['class' => 'badge bg-success-lt', 'text' => 'ENTREGUE',      'icon' => 'ti ti-check'],
            'in_transit'     => ['class' => 'badge bg-blue-lt',    'text' => 'EM TRÂNSITO',   'icon' => 'ti ti-truck'],
            'not_found'      => ['class' => 'badge bg-yellow-lt',  'text' => 'SEM RASTRO',    'icon' => 'ti ti-search'],
            'http_error'     => ['class' => 'badge bg-red-lt',     'text' => 'ERRO API',      'icon' => 'ti ti-alert-circle'],
            'not_integrated' => ['class' => 'badge bg-muted-lt',   'text' => 'NÃO INTEGRADO', 'icon' => 'ti ti-ban'],
            'default'        => ['class' => 'badge bg-secondary-lt', 'text' => 'PROCESSANDO',   'icon' => 'ti ti-dots'],
        ];

        // Aqui transformamos os dados
        $nfes->getCollection()->transform(function ($nf) use ($integradas, $statusMap) {

            $transpNome = strtoupper(trim($nf->transportadora_nome ?? ''));

            // 1 — Transportadora própria
            if (str_contains($transpNome, 'PROPRIA')) {
                $nf->ui_class   = $statusMap['delivered']['class'];
                $nf->ui_text    = $statusMap['delivered']['text'];
                $nf->ui_icon    = $statusMap['delivered']['icon'];
                $nf->ui_message = 'Entrega interna (transportadora própria)';
                $nf->ui_date    = $nf->created_at;
                return $nf;
            }

            // 2 — Transportadora vazia
            if ($transpNome === '') {
                $nf->ui_class   = 'badge bg-purple-lt';
                $nf->ui_text    = 'DOCUMENTO INTERNO';
                $nf->ui_icon    = 'ti ti-file';
                $nf->ui_message = 'Documento interno — sem transporte vinculado.';
                $nf->ui_date    = $nf->created_at;
                return $nf;
            }

            // 3 — Não integrada
            if (!collect($integradas)->contains(fn($x) => str_contains($transpNome, $x))) {
                $nf->ui_class   = $statusMap['not_integrated']['class'];
                $nf->ui_text    = $statusMap['not_integrated']['text'];
                $nf->ui_icon    = $statusMap['not_integrated']['icon'];
                $nf->ui_message = 'Transportadora não homologada.';
                $nf->ui_date    = null;
                return $nf;
            }

            // 4 — Integrada → usar tracking real
            $last = $nf->lastTracking;
            $statusKey = $last->status ?? 'not_found';
            $mapped = $statusMap[$statusKey] ?? $statusMap['default'];

            $nf->ui_class   = $mapped['class'];
            $nf->ui_text    = $mapped['text'];
            $nf->ui_icon    = $mapped['icon'];
            $nf->ui_message = $last->mensagem ?? '---';
            $nf->ui_date    = $last->data_evento ?? null;

            return $nf;
        });

        return view('transpNfes.index', compact('nfes'));
    }
}
