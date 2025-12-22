<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Processo Recusado</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; padding: 20px;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                       style="background: #ffffff; border-radius: 6px; padding: 20px;">

                    {{-- HEADER --}}
                    <tr>
                        <td style="border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
                            <h2 style="margin: 0; color: #b91c1c;">
                                🚫 Processo Recusado pelo Fiscal
                            </h2>
                        </td>
                    </tr>

                    {{-- BODY --}}
                    <tr>
                        <td style="padding-top: 15px; color: #374151; font-size: 14px;">
                            <p>
                                O processo abaixo foi <strong>recusado pelo setor Fiscal</strong>.
                            </p>

                            <table width="100%" cellpadding="6" cellspacing="0"
                                   style="border-collapse: collapse; margin-top: 10px;">
                                <tr>
                                    <td width="35%"><strong>ID do Processo:</strong></td>
                                    <td>#{{ $process->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo:</strong></td>
                                    <td>{{ $process->processType->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cliente:</strong></td>
                                    <td>{{ $process->cliente_nome ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>CNPJ:</strong></td>
                                    <td>{{ $process->cliente_cnpj ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Motivo:</strong></td>
                                    <td>{{ $process->motivo ?? $process->workflowReason->name ?? '-' }}</td>
                                </tr>
                            </table>

                            {{-- OBSERVAÇÃO --}}
                            <div style="margin-top: 15px; padding: 12px;
                                        background: #fef2f2; border-left: 4px solid #b91c1c;">
                                <strong>Observação do Fiscal:</strong>
                                <p style="margin: 5px 0 0 0;">
                                    {{ $process->observacoes }}
                                </p>
                            </div>

                            <p style="margin-top: 20px;">
                                O processo foi encerrado e pode ser revisado ou reaberto conforme necessário.
                            </p>
                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="padding-top: 20px; border-top: 1px solid #e5e7eb;
                                   font-size: 12px; color: #6b7280;">
                            <p style="margin: 0;">
                                Este é um e-mail automático do sistema <strong>CoreFlow</strong>.
                            </p>
                            <p style="margin: 4px 0 0 0;">
                                Não responda este e-mail.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
