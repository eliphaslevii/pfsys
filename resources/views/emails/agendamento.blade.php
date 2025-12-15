<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agendamento de Coleta</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f6f8; padding:20px;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">

                <table width="700" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:6px; overflow:hidden;">

                    {{-- HEADER --}}
                    <tr>
                        <td style="background:#1f2937; padding:16px 24px; color:#ffffff;">
                            <h2 style="margin:0; font-size:18px;">
                                📦 Agendamento de Coleta de NF-e
                            </h2>
                        </td>
                    </tr>

                    {{-- CONTEÚDO --}}
                    <tr>
                        <td style="padding:24px; color:#333;">

                            <p style="margin-top:0;">
                                Prezados,
                            </p>

                            <p>
                                Segue abaixo o <strong>agendamento de coleta</strong> das notas fiscais relacionadas.
                            </p>

                            {{-- DADOS DO AGENDAMENTO --}}
                            <table width="100%" cellpadding="6" cellspacing="0" style="margin:16px 0; font-size:14px;">
                                <tr>
                                    <td width="200"><strong>Transportadora:</strong></td>
                                    <td>{{ $agendamento->transportadora_nome }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>{{ strtoupper($agendamento->status) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Data de Criação:</strong></td>
                                    <td>{{ $agendamento->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>

                            {{-- TABELA DE NFES --}}
                            <h3 style="margin-top:24px;">Notas Fiscais</h3>

                            <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:13px;">
                                <thead>
                                    <tr style="background:#f0f0f0;">
                                        <th align="left" style="border:1px solid #ddd;">Número</th>
                                        <th align="left" style="border:1px solid #ddd;">Série</th>
                                        <th align="left" style="border:1px solid #ddd;">Chave de Acesso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($agendamento->nfes as $nfe)
                                        <tr>
                                            <td style="border:1px solid #ddd;">
                                                {{ $nfe->numero }}
                                            </td>
                                            <td style="border:1px solid #ddd;">
                                                {{ $nfe->serie ?? '1' }}
                                            </td>
                                            <td style="border:1px solid #ddd; font-family: monospace; font-size:12px;">
                                                {{ $nfe->chave }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <p style="margin-top:24px;">
                                Os espelhos das NF-es seguem anexos neste e-mail em formato PDF (arquivo ZIP).
                            </p>

                            <p>
                                Em caso de dúvidas, favor entrar em contato com o setor de logística.
                            </p>

                            <p style="margin-bottom:0;">
                                Atenciosamente,<br>
                                <strong>Equipe de Logística</strong>
                            </p>

                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="background:#f9fafb; padding:12px; font-size:12px; color:#666; text-align:center;">
                            Este é um e-mail automático. Não responda.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
