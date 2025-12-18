@php
$context = $context ?? 'default';
@endphp

<div class="mt-4 pt-3 border-top">

    {{-- BOTÃO --}}
    <button
        type="button"
        class="btn btn-outline-primary w-100 d-flex justify-content-between align-items-center"
        data-bs-toggle="collapse"
        data-bs-target="#xmlDetailsArea_{{ $context }}"
        aria-expanded="false"
        aria-controls="xmlDetailsArea_{{ $context }}">

        <span class="fw-bold">
            <i class="ti ti-list-details me-2"></i>
            Detalhes da Nota Fiscal
        </span>

        <i class="ti ti-chevron-down transition"></i>
    </button>

    {{-- COLLAPSE --}}
    <div class="collapse mt-3" id="xmlDetailsArea_{{ $context }}">

        <div class="card shadow-sm">

            <div class="card-header bg-light d-flex align-items-center gap-2">
                <i class="ti ti-file-description text-primary"></i>
                <span class="fw-semibold">Dados extraídos do XML</span>
            </div>

            <div class="card-body">

                {{-- INFO --}}
                <div class="row g-2 mb-3">

                    @php
                    $fields = [
                    ['Cliente', "client-name-display_$context"],
                    ['CNPJ', "client-cnpj-display_$context"],
                    ['Protocolo', "nprot-display_$context"],
                    ['NF Saída', "nf-saida-display_$context"],
                    ['NF Devolução', "inf-nfd-display_$context"],
                    ['NF Original', "inf-nfo-display_$context"],
                    ];
                    @endphp

                    @foreach($fields as [$label, $id])
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="border rounded-2 p-2 position-relative copy-box">

                            <small class="text-muted">{{ $label }}</small>

                            <div class="fw-semibold text-break pe-4"
                                id="{{ $id }}"
                                data-copy-value>
                                N/A
                            </div>

                            {{-- BOTÃO COPY --}}
                            <button
                                type="button"
                                class="btn btn-sm btn-ghost-secondary position-absolute top-0 end-0 m-1 copy-btn"
                                title="Copiar"
                                data-copy-target="{{ $id }}">
                                <i class="ti ti-copy"></i>
                            </button>

                        </div>
                    </div>
                    @endforeach

                    {{-- INFO COMPLEMENTAR --}}
                    <div class="col-12">
                        <div class="border rounded-2 p-2 position-relative copy-box">
                            <small class="text-muted">Informações Complementares</small>

                            <div id="inf-cpl-display_{{ $context }}"
                                class="small text-break pe-4"
                                data-copy-value>
                                N/A
                            </div>

                            <button
                                type="button"
                                class="btn btn-sm btn-ghost-secondary position-absolute top-0 end-0 m-1 copy-btn"
                                title="Copiar"
                                data-copy-target="inf-cpl-display_{{ $context }}">
                                <i class="ti ti-copy"></i>
                            </button>
                        </div>
                    </div>

                </div>

                {{-- TABELA --}}
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light small text-secondary">
                            <tr>
                                <th>Artigo</th>
                                <th>Descrição</th>
                                <th class="d-none d-md-table-cell">NCM</th>
                                <th class="d-none d-lg-table-cell">NF Saída</th>
                                <th class="text-end">Qtd.</th>
                                <th class="text-end">Preço</th>
                            </tr>
                        </thead>

                        <tbody id="product-table-body_{{ $context }}">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    <i class="ti ti-package-off fs-4 d-block mb-1"></i>
                                    Aguardando XML
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- SCRIPT LOCAL (pode mover depois pro JS global se quiser) --}}
@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.copy-btn');
        if (!btn) return;

        const targetId = btn.dataset.copyTarget;
        const el = document.getElementById(targetId);
        if (!el) return;

        const text = el.textContent.trim();
        if (!text || text === 'N/A') return;

        const success = () => {
            btn.innerHTML = '<i class="ti ti-check text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = '<i class="ti ti-copy"></i>';
            }, 1200);
        };

        const fallbackCopy = () => {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                document.execCommand('copy');
                success();
            } catch (err) {
                console.error('Copy fallback failed', err);
            }

            document.body.removeChild(textarea);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text)
                .then(success)
                .catch(fallbackCopy);
        } else {
            fallbackCopy();
        }
    });
</script>

@endpush