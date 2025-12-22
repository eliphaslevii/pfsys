<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processo aguardando ação</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 650px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background-color: #427cac;
            color: #fff;
            padding: 16px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        .content {
            padding: 24px;
        }

        .content h2 {
            margin-top: 0;
            color: #427cac;
            font-size: 16px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .info-table td {
            padding: 8px 10px;
            border: 1px solid #eee;
        }

        .info-table td:first-child {
            background-color: #f3f6fa;
            font-weight: bold;
            width: 35%;
        }

        .cta {
            margin-top: 24px;
            text-align: center;
        }

        .cta a {
            display: inline-block;
            background-color: #427cac;
            color: #fff;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .footer {
            font-size: 12px;
            color: #777;
            text-align: center;
            padding: 12px;
            border-top: 1px solid #eee;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            Processo aguardando ação
        </div>

        <div class="content">
            <h2>Olá,</h2>

            <p>
                Um processo de <strong>{{ $process->processType->name }}</strong>
                (<strong>#{{ $process->id }}</strong>) avançou no fluxo
                e agora está sob responsabilidade do seu setor.
            </p>

            <p>
                <strong>Etapa atual:</strong> {{ $nextStep }}
            </p>

            <table class="info-table">
                <tr>
                    <td>Cliente</td>
                    <td>{{ $process->cliente_nome }}</td>
                </tr>
                <tr>
                    <td>CNPJ</td>
                    <td>{{ $process->cliente_cnpj }}</td>
                </tr>
                <tr>
                    <td>Motivo</td>
                    <td>{{ $process->motivo }}</td>
                </tr>
                <tr>
                    <td>Código de erro</td>
                    <td>{{ $process->codigo_erro ?? '—' }}</td>
                </tr>

                @if($process->nf_saida)
                <tr>
                    <td>NF Saída</td>
                    <td>{{ $process->nf_saida }}</td>
                </tr>
                @endif

                @if($process->nf_devolucao)
                <tr>
                    <td>NF Devolução</td>
                    <td>{{ $process->nf_devolucao }}</td>
                </tr>
                @endif

                @if($process->nfo)
                <tr>
                    <td>NF Original</td>
                    <td>{{ $process->nfo }}</td>
                </tr>
                @endif
            </table>

            <div class="cta">
                {{-- emails/process-advanced.blade.php --}}
                <a href="{{ $url }}">Acessar processos</a>

            </div>
        </div>

        <div class="footer">
            Este é um e-mail automático do sistema <strong>B-Syst</strong>.<br>
            Não responda esta mensagem.
        </div>
    </div>

</body>

</html>