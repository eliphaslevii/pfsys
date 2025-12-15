{{-- ESPÉLHO DANFE GERADO AUTOMATICAMENTE --}}

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    {{-- CSS ORIGINAL INTACTO --}}
    <style type="text/css">
        @media print {
            @page {
                margin-left: 15mm;
                margin-right: 15mm;
            }

            footer {
                page-break-after: always;
            }
        }

        * {
            margin: 0;
        }

        .ui-widget-content {
            border: none !important;
        }

        @font-face {
            font-family: 'code128';
            src: url('{{ asset("fonts/code128.ttf") }}') format('truetype');
        }

        .barcode {
            font-family: 'code128';
            font-size: 22px;
            text-align: center;
            display: block;
        }

        .nfe-square {
            margin: 0 auto 2cm;
            box-sizing: border-box;
            width: 2cm;
            height: 1cm;
            border: 1px solid #000;
        }

        .nfeArea.page {
            width: 18cm;
            position: relative;
            font-family: "Times New Roman", serif;
            color: #000;
            margin: 0 auto;
            overflow: hidden;
        }

        .nfeArea .font-12 {
            font-size: 12pt;
        }

        .nfeArea .font-8 {
            font-size: 8pt;
        }

        .nfeArea .bold {
            font-weight: bold;
        }

        .nfeArea .area-name {
            font-family: "Times New Roman", serif;
            color: #000;
            font-weight: bold;
            margin: 5px 0 0;
            font-size: 6pt;
            text-transform: uppercase;
        }

        .nfeArea .txt-upper {
            text-transform: uppercase;
        }

        .nfeArea .txt-center {
            text-align: center;
        }

        .nfeArea .txt-right {
            text-align: right;
        }

        .nfeArea .nf-label {
            text-transform: uppercase;
            margin-bottom: 3px;
            display: block;
        }

        .nfeArea .nf-label.label-small {
            letter-spacing: -0.5px;
            font-size: 4pt;
        }

        .nfeArea .info {
            font-weight: bold;
            font-size: 8pt;
            display: block;
            line-height: 1em;
        }

        .nfeArea table {
            font-family: "Times New Roman", serif;
            color: #000;
            font-size: 5pt;
            border-collapse: collapse;
            width: 100%;
            border-color: #000;
            border-radius: 5px;
        }

        .nfeArea .no-top {
            margin-top: -1px;
        }

        .nfeArea .mt-table {
            margin-top: 3px;
        }

        .nfeArea .valign-middle {
            vertical-align: middle;
        }

        .nfeArea td {
            vertical-align: top;
            box-sizing: border-box;
            overflow: hidden;
            border-color: #000;
            padding: 1px;
            height: 5mm;
        }

        .nfeArea .tserie {
            width: 32.2mm;
            vertical-align: middle;
            font-size: 8pt;
            font-weight: bold;
        }

        .nfeArea .tserie span {
            display: block;
        }

        .nfeArea .tserie h3 {
            display: inline-block;
        }

        .client_logo {
            max-width: 30mm;
            max-height: 25mm;
        }
    </style>
</head>

<body>

    <div class="page nfeArea">

        {{-- TARJAS DE CANCELADA / SEM VALIDADE --}}

        <div class="boxFields" style="padding-top: 20px;">

            {{-- RECEBIMENTO --}}
            <table cellpadding="0" cellspacing="0" border="1">
                <tbody>
                    <tr>
                        <td colspan="2" class="txt-upper">
                            Recebemos de {{ $nfe['emitente']['nome'] }} os produtos e serviços constantes na nota fiscal indicada ao lado
                        </td>
                        <td rowspan="2" class="tserie txt-center">
                            <span class="font-12" style="margin-bottom: 5px;">NF-e</span>
                            <span>Nº {{ $nfe['numero'] }}</span>
                            <span>Série {{ $nfe['serie'] }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 32mm">
                            <span class="nf-label">Data de recebimento</span>
                        </td>
                        <td style="width: 124.6mm">
                            <span class="nf-label">Identificação de assinatura do Recebedor</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr class="hr-dashed" />

            {{-- CABEÇALHO DO DANFE (LOGO / EMITENTE / CHAVE) --}}
            <table cellpadding="0" cellspacing="0" border="1">
                <tbody>
                    <tr>
                        {{-- LOGO --}}
                        <td rowspan="3" style="width: 30mm">
                            @if($nfe['logo_base64'])
                            <img
                                src="data:image/png;base64,{{ $nfe['logo_base64'] }}"
                                style="max-width: 28mm; max-height: 18mm;"
                                alt="Logo Cliente">
                            @endif

                        </td>
                        {{-- EMITENTE --}}
                        <td rowspan="3" style="width: 46mm; font-size: 7pt;" class="txt-center">
                            <span class="mb2 bold block">{{ $nfe['emitente']['nome'] }}</span>
                            <span class="block">{{ $nfe['emitente']['endereco'] }}</span>
                            <span class="block">
                                {{ $nfe['emitente']['municipio'] }} - {{ $nfe['emitente']['cep'] }}
                            </span>
                            <span class="block">
                                {{ $nfe['emitente']['municipio'] }} - {{ $nfe['emitente']['uf'] }}
                            </span>
                            <span class="block">
                                CNPJ: {{ $nfe['emitente']['cnpj'] }} - IE: {{ $nfe['emitente']['ie'] }}
                            </span>
                        </td>

                        {{-- DANFE HEADER --}}
                        <td rowspan="3" class="txtc txt-upper" style="width: 34mm; height: 29.5mm;">
                            <h3 class="title">Danfe</h3>
                            <p class="mb2">Documento auxiliar da Nota Fiscal Eletrônica </p>

                            <p class="entradaSaida mb2">
                                <span class="identificacao">
                                    <span>{{ $nfe['tipo_operacao'] }}</span>
                                </span>
                                <span class="legenda">
                                    <span>0 - Entrada</span>
                                    <span>1 - Saída</span>
                                </span>
                            </p>

                            <p>
                                <span class="block bold">
                                    <span>Nº</span>
                                    <span>{{ $nfe['numero'] }}</span>
                                </span>
                                <span class="block bold">
                                    <span>SÉRIE:</span>
                                    <span>{{ $nfe['serie'] }}</span>
                                </span>
                                <span class="block">
                                    Página 1 de 1
                                </span>
                            </p>
                        </td>

                        {{-- CHAVE E BARCODE --}}
                        <td class="txt-upper" style="width: 85mm;">
                            <span class="nf-label">Controle do Fisco</span>
                            <span class="codigo">
                                {{-- Barcode pode ser gerado externamente --}}
                                {{ $nfe['chave'] }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td class="txt-center">
                            @if(!empty($nfe['barcode_base64']))
                            <img
                                src="data:image/png;base64,{{ $nfe['barcode_base64'] }}"
                                style="width:300px;margin-top:10px">
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="txt-center valign-middle">
                            <span class="block">Consulta de autenticidade no portal nacional da NF-e</span>
                            www.nfe.fazenda.gov.br/portal
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- NATUREZA DA OPERAÇÃO --}}
            <table cellpadding="0" cellspacing="0" class="boxNaturezaOperacao no-top" border="1">
                <tbody>
                    <tr>
                        <td>
                            <span class="nf-label">NATUREZA DA OPERAÇÃO</span>
                            <span class="info">{{ $nfe['natureza_operacao'] }}</span>
                        </td>

                        <td style="width: 84.7mm;">
                            <span class="nf-label">PROTOCOLO</span>
                            <span class="info">{{ $nfe['protocolo_autorizacao'] ?? '' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- INSCRIÇÕES --}}
            <table cellpadding="0" cellspacing="0" class="boxInscricao no-top" border="1">
                <tbody>
                    <tr>
                        <td>
                            <span class="nf-label">INSCRIÇÃO ESTADUAL</span>
                            <span class="info">{{ $nfe['emitente']['ie'] }}</span>
                        </td>

                        <td style="width: 67.5mm;">
                            <span class="nf-label">INSCRIÇÃO ESTADUAL SUBST. TRIB.</span>
                            <span class="info">{{ $nfe['emitente']['ie_st'] ?? '' }}</span>
                        </td>

                        <td style="width: 64.3mm">
                            <span class="nf-label">CNPJ</span>
                            <span class="info">{{ $nfe['emitente']['cnpj'] }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- DESTINATÁRIO --}}
            <p class="area-name">Destinatário/Emitente</p>

            <table cellpadding="0" cellspacing="0" class="boxDestinatario" border="1">
                <tbody>
                    <tr>
                        <td class="pd-0">
                            <table cellpadding="0" cellspacing="0" border="1">
                                <tbody>
                                    <tr>
                                        <td>
                                            <span class="nf-label">NOME/RAZÃO SOCIAL</span>
                                            <span class="info">{{ $nfe['destinatario']['nome'] }}</span>
                                        </td>

                                        <td style="width: 40mm">
                                            <span class="nf-label">CNPJ/CPF</span>
                                            <span class="info">{{ $nfe['destinatario']['cnpj'] }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>

                        <td style="width: 22mm">
                            <span class="nf-label">DATA DE EMISSÃO</span>
                            <span class="info">{{ $nfe['data_emissao'] }}</span>
                        </td>
                    </tr>

                    <tr>
                        <td class="pd-0">
                            <table cellpadding="0" cellspacing="0" border="1">
                                <tbody>
                                    <tr>
                                        <td>
                                            <span class="nf-label">ENDEREÇO</span>
                                            <span class="info">{{ $nfe['destinatario']['endereco'] }}</span>
                                        </td>

                                        <td style="width: 47mm;">
                                            <span class="nf-label">BAIRRO/DISTRITO</span>
                                            <span class="info">{{ $nfe['destinatario']['bairro'] ?? '' }}</span>
                                        </td>

                                        <td style="width: 37.2mm">
                                            <span class="nf-label">CEP</span>
                                            <span class="info">{{ $nfe['destinatario']['cep'] }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>

                        <td>
                            <span class="nf-label">DATA DE ENTR./SAÍDA</span>
                            <span class="info">{{ $nfe['data_emissao'] }}</span>
                        </td>
                    </tr>

                    <tr>
                        <td class="pd-0">
                            <table cellpadding="0" cellspacing="0" style="margin-bottom: -1px;" border="1">
                                <tbody>
                                    <tr>
                                        <td>
                                            <span class="nf-label">MUNICÍPIO</span>
                                            <span class="info">{{ $nfe['destinatario']['municipio'] }}</span>
                                        </td>

                                        <td style="width: 34mm">
                                            <span class="nf-label">FONE/FAX</span>
                                            <span class="info">{{ $nfe['destinatario']['telefone'] ?? '' }}</span>
                                        </td>

                                        <td style="width: 28mm">
                                            <span class="nf-label">UF</span>
                                            <span class="info">{{ $nfe['destinatario']['uf'] }}</span>
                                        </td>

                                        <td style="width: 51mm">
                                            <span class="nf-label">INSCRIÇÃO ESTADUAL</span>
                                            <span class="info">{{ $nfe['destinatario']['ie'] }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>

                        <td>
                            <span class="nf-label">HORA ENTR./SAÍDA</span>
                            <span>{{ $nfe['hora_emissao'] ?? '' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            {{-- FATURA --}}
            <div class="boxFatura">
                <p class="area-name">Fatura</p>

                {{-- Caso exista fatura --}}
                @if (!empty($nfe['fatura']['numero']))
                <table cellpadding="0" cellspacing="0" border="1">
                    <tbody>
                        <tr>
                            <td>
                                <span class="nf-label">NÚMERO</span>
                                <span class="info">{{ $nfe['fatura']['numero'] }}</span>
                            </td>

                            <td>
                                <span class="nf-label">VALOR</span>
                                <span class="info">{{ number_format($nfe['fatura']['valor'], 2, ',', '.') }}</span>
                            </td>

                            <td>
                                <span class="nf-label">VENCIMENTO</span>
                                <span class="info">{{ $nfe['fatura']['vencimento'] }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                @else
                <p style="font-size: 6pt;">Sem informação de fatura.</p>
                @endif

            </div>

            {{-- CÁLCULO DO IMPOSTO --}}
            <p class="area-name">Cálculo do imposto</p>

            <div class="wrapper-table">
                <table cellpadding="0" cellspacing="0" border="1" class="boxImposto">
                    <tbody>
                        <tr>
                            <td>
                                <span class="nf-label label-small">BASE DE CÁLC. ICMS</span>
                                <span class="info">{{ $nfe['totais']['base_icms'] ?? '' }}</span>
                            </td>

                            <td>
                                <span class="nf-label">VALOR ICMS</span>
                                <span class="info">{{ $nfe['totais']['valor_icms'] ?? '' }}</span>
                            </td>

                            <td>
                                <span class="nf-label label-small">BASE ICMS ST</span>
                                <span class="info">{{ $nfe['totais']['base_st'] ?? '' }}</span>
                            </td>

                            <td>
                                <span class="nf-label">VALOR ICMS ST</span>
                                <span class="info">{{ $nfe['totais']['valor_st'] ?? '' }}</span>
                            </td>

                            <td>
                                <span class="nf-label label-small">IMP. IMPORTAÇÃO</span>
                                <span class="info"></span>
                            </td>

                            <td>
                                <span class="nf-label label-small">ICMS UF REMET.</span>
                                <span class="info"></span>
                            </td>

                            <td>
                                <span class="nf-label">VALOR FCP</span>
                                <span class="info">{{ $nfe['totais']['valor_fcp'] ?? '' }}</span>
                            </td>

                            <td>
                                <span class="nf-label">VALOR PIS</span>
                                <span class="info">{{ $nfe['totais']['valor_pis'] ?? '' }}</span>
                            </td>

                            <td>
                                <span class="nf-label label-small">V. TOTAL PRODUTOS</span>
                                <span class="info">{{ number_format($nfe['totais']['valor_produtos'], 2, ',', '.') }}</span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <span class="nf-label">VALOR FRETE</span>
                                <span class="info">{{ number_format($nfe['totais']['valor_frete'], 2, ',', '.') }}</span>
                            </td>

                            <td>
                                <span class="nf-label">VALOR SEGURO</span>
                                <span class="info">{{ number_format($nfe['totais']['valor_seguro'], 2, ',', '.') }}</span>
                            </td>

                            <td>
                                <span class="nf-label">DESCONTO</span>
                                <span class="info">{{ number_format($nfe['totais']['valor_desconto'], 2, ',', '.') }}</span>
                            </td>

                            <td>
                                <span class="nf-label">OUTRAS DESP.</span>
                                <span class="info">{{ $nfe['totais']['valor_outras'] ?? '' }}</span>
                            </td>

                            <td>
                                <span class="nf-label">VALOR IPI</span>
                                <span class="info">{{ $nfe['totais']['valor_ipi'] ?? '' }}</span>
                            </td>

                            <td>
                                <span class="nf-label">ICMS DEST.</span>
                                <span class="info"></span>
                            </td>

                            <td>
                                <span class="nf-label label-small">V. APROX. TRIBUTO</span>
                                <span class="info">{{ $nfe['totais']['valor_tributos'] }}</span>
                            </td>

                            <td>
                                <span class="nf-label label-small">CONFINS</span>
                                <span class="info">{{ $nfe['totais']['valor_cofins'] ?? '' }}</span>
                            </td>

                            <td>
                                <span class="nf-label label-small">VALOR TOTAL NF</span>
                                <span class="info">{{ number_format($nfe['totais']['valor_total'], 2, ',', '.') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- TRANSPORTADOR --}}
            <p class="area-name">Transportador / Volumes transportados</p>

            <table cellpadding="0" cellspacing="0" border="1">
                <tbody>
                    <tr>
                        <td>
                            <span class="nf-label">RAZÃO SOCIAL</span>
                            <span class="info">{{ $nfe['transporte']['nome'] }}</span>
                        </td>
                        @php
                        $modFreteMap = [
                        '0' => 'Emitente',
                        '1' => 'Destinatário',
                        '2' => 'Terceiros',
                        '9' => 'Sem frete',
                        ];
                        @endphp

                        <td class="freteConta" style="width: 32mm">
                            <span class="nf-label">FRETE POR CONTA</span>

                            <div class="border">
                                <span class="info">
                                    {{ $modFreteMap[$nfe['transporte']['mod_frete']] ?? '' }}
                                </span>
                            </div>
                        </td>


                        <td style="width: 17.3mm">
                            <span class="nf-label">CÓDIGO ANTT</span>
                            <span class="info"></span>
                        </td>

                        <td style="width: 24.5mm">
                            <span class="nf-label">PLACA</span>
                            <span class="info"></span>
                        </td>

                        <td style="width: 11.3mm">
                            <span class="nf-label">UF</span>
                            <span class="info"></span>
                        </td>

                        <td style="width: 29.5mm">
                            <span class="nf-label">CNPJ/CPF</span>
                            <span class="info">{{ $nfe['transporte']['cnpj'] }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="1" class="no-top">
                <tbody>
                    <tr>
                        <td class="field quantidade">
                            <span class="nf-label">QUANTIDADE</span>
                            <span class="info">{{ $nfe['transporte']['quantidade'] }}</span>
                        </td>

                        <td style="width: 31.4mm">
                            <span class="nf-label">ESPÉCIE</span>
                            <span class="info">{{ $nfe['transporte']['especie'] }}</span>
                        </td>

                        <td style="width: 31.5mm">
                            <span class="nf-label">PESO BRUTO</span>
                            <span class="info">{{ $nfe['transporte']['peso_bruto'] }}</span>
                        </td>

                        <td style="width: 31.5mm">
                            <span class="nf-label">PESO LÍQUIDO</span>
                            <span class="info">{{ $nfe['transporte']['peso_liquido'] }}</span>
                        </td>

                        {{-- Campos inexistentes no banco --}}
                        <td style="width: 31mm">
                            <span class="nf-label">MARCA</span>
                            <span class="info"></span>
                        </td>

                        <td style="width: 31mm">
                            <span class="nf-label">NUMERAÇÃO</span>
                            <span class="info"></span>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- ENDEREÇO DO TRANSPORTADOR --}}
            <table cellpadding="0" cellspacing="0" border="1" class="no-top">
                <tbody>
                    <tr>
                        <td class="field endereco">
                            <span class="nf-label">ENDEREÇO</span>
                            <span class="content-spacer info">{{ $nfe['transporte']['endereco'] ?? '' }}</span>
                        </td>

                        <td style="width: 32mm">
                            <span class="nf-label">MUNICÍPIO</span>
                            <span class="info">{{ $nfe['transporte']['municipio'] ?? '' }}</span>
                        </td>

                        <td style="width: 31mm">
                            <span class="nf-label">UF</span>
                            <span class="info">{{ $nfe['transporte']['uf'] ?? '' }}</span>
                        </td>

                        <td style="width: 51.4mm">
                            <span class="nf-label">INSC. ESTADUAL</span>
                            <span class="info">{{ $nfe['transporte']['ie'] ?? '' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- VOLUMES --}}
            <table cellpadding="0" cellspacing="0" border="1" class="no-top">
                <tbody>
                    <tr>
                        <td class="field quantidade">
                            <span class="nf-label">QUANTIDADE</span>
                            <span class="content-spacer info">{{ $nfe['transporte']['quantidade'] }}</span>
                        </td>

                        <td style="width: 31.4mm">
                            <span class="nf-label">ESPÉCIE</span>
                            <span class="info">{{ $nfe['transporte']['especie'] }}</span>
                        </td>

                        <td style="width: 31mm">
                            <span class="nf-label">MARCA</span>
                            <span class="info">{{ $nfe['transporte']['marca'] ?? '' }}</span>
                        </td>

                        <td style="width: 31.5mm">
                            <span class="nf-label">NUMERAÇÃO</span>
                            <span class="info">{{ $nfe['transporte']['numeracao'] ?? '' }}</span>
                        </td>

                        <td style="width: 31.5mm">
                            <span class="nf-label">PESO BRUTO</span>
                            <span class="info">{{ $nfe['transporte']['peso_bruto'] }}</span>
                        </td>

                        <td style="width: 32.5mm">
                            <span class="nf-label">PESO LÍQUIDO</span>
                            <span class="info">{{ $nfe['transporte']['peso_liquido'] }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- PRODUTOS / SERVIÇOS --}}
            <p class="area-name">Dados do produto/serviço</p>

            <div class="wrapper-border">
                <table cellpadding="0" cellspacing="0" border="1" class="boxProdutoServico">
                    <thead class="listProdutoServico" id="table">
                        <tr class="titles">
                            <th class="cod" style="width: 15.5mm">CÓDIGO</th>
                            <th class="descrit" style="width: 66.1mm">DESCRIÇÃO DO PRODUTO/SERVIÇO</th>
                            <th class="ncmsh">NCM/SH</th>
                            <th class="cst">CST</th>
                            <th class="cfop">CFOP</th>
                            <th class="un">UN</th>
                            <th class="amount">QTD.</th>
                            <th class="valUnit">VLR.UNIT</th>
                            <th class="valTotal">VLR.TOTAL</th>
                            <th class="bcIcms">BC ICMS</th>
                            <th class="valIcms">VLR.ICMS</th>
                            <th class="valIpi">VLR.IPI</th>
                            <th class="aliqIcms">ALIQ.ICMS</th>
                            <th class="aliqIpi">ALIQ.IPI</th>
                        </tr>
                    </thead>

                    <tbody>
                        {{-- LISTA DE ITENS DA NFE --}}
                        @foreach ($nfe['itens'] as $item)
                        <tr>
                            <td>{{ $item['codigo'] }}</td>
                            <td>{{ $item['descricao'] }}</td>
                            <td>{{ $item['ncm'] }}</td>

                            {{-- CST (ICMS) --}}
                            <td>{{ $item['icms']['cst'] ?? '' }}</td>

                            <td>{{ $item['cfop'] }}</td>
                            <td>{{ $item['unidade'] }}</td>
                            <td>{{ number_format($item['quantidade'], 4, ',', '.') }}</td>
                            <td>{{ number_format($item['valor_unitario'], 4, ',', '.') }}</td>
                            <td>{{ number_format($item['valor_total'], 2, ',', '.') }}</td>

                            <td>{{ $item['icms']['v_bc'] ?? '' }}</td>
                            <td>{{ $item['icms']['v_icms'] ?? '' }}</td>

                            <td>{{ $item['ipi']['v_ipi'] ?? '' }}</td>

                            <td>{{ $item['icms']['p_icms'] ?? '' }}</td>
                            <td>{{ $item['ipi']['p_ipi'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            {{-- ISSQN --}}
            <p class="area-name">Cálculo do ISSQN</p>

            <table cellpadding="0" cellspacing="0" border="1" class="boxIssqn">
                <tbody>
                    <tr>
                        <td class="field inscrMunicipal">
                            <span class="nf-label">INSCRIÇÃO MUNICIPAL</span>
                            <span class="info txt-center">{{ $nfe['emitente']['im'] ?? '' }}</span>
                        </td>

                        <td class="field valorTotal">
                            <span class="nf-label">VALOR TOTAL SERVIÇOS</span>
                            <span class="info txt-right">{{ $nfe['totais']['valor_servicos'] ?? '' }}</span>
                        </td>

                        <td class="field baseCalculo">
                            <span class="nf-label">BASE CÁLCULO ISSQN</span>
                            <span class="info txt-right">{{ $nfe['totais']['base_issqn'] ?? '' }}</span>
                        </td>

                        <td class="field valorIssqn">
                            <span class="nf-label">VALOR ISSQN</span>
                            <span class="info txt-right">{{ $nfe['totais']['valor_issqn'] ?? '' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- DADOS ADICIONAIS --}}
            <p class="area-name">Dados adicionais</p>

            <table cellpadding="0" cellspacing="0" border="1" class="boxDadosAdicionais">
                <tbody>
                    <tr>
                        <td class="field infoComplementar">
                            <span class="nf-label">INFORMAÇÕES COMPLEMENTARES</span>
                            <span>{{ $nfe['informacoes'] }}</span>
                        </td>

                        <td class="field reservaFisco" style="width: 85mm; height: 24mm">
                            <span class="nf-label">RESERVA AO FISCO</span>
                            <span></span>
                        </td>
                    </tr>
                </tbody>
            </table>
            {{-- TRANSPORTADOR — CAMPOS REAIS DO BANCO --}}
            <table cellpadding="0" cellspacing="0" border="1" class="no-top">
                <tbody>
                    <tr>
                        <td class="field quantidade">
                            <span class="nf-label">QUANTIDADE</span>
                            <span class="content-spacer info">{{ $nfe['transporte']['quantidade'] }}</span>
                        </td>

                        <td style="width: 31.4mm">
                            <span class="nf-label">ESPÉCIE</span>
                            <span class="info">{{ $nfe['transporte']['especie'] }}</span>
                        </td>

                        <td style="width: 31.5mm">
                            <span class="nf-label">PESO BRUTO</span>
                            <span class="info">{{ $nfe['transporte']['peso_bruto'] }}</span>
                        </td>

                        <td style="width: 31.5mm">
                            <span class="nf-label">PESO LÍQUIDO</span>
                            <span class="info">{{ $nfe['transporte']['peso_liquido'] }}</span>
                        </td>

                        {{-- CAMPOS QUE NÃO EXISTEM NO SEU MIGRATION SÃO REMOVIDOS --}}
                        <td style="width: 31mm">
                            <span class="nf-label">MARCA</span>
                            <span class="info"></span>
                        </td>

                        <td style="width: 31mm">
                            <span class="nf-label">NUMERAÇÃO</span>
                            <span class="info"></span>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- (continuação da parte final da estrutura) --}}
            <footer>
                <table cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td style="text-align: right">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </footer>

        </div> {{-- FIM DO boxFields --}}
    </div> {{-- FIM DA PÁGINA --}}

</body>

</html>