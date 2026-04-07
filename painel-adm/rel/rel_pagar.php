<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../../conexao.php';
}

$dataInicial = !empty($_GET['dataInicial']) ? $_GET['dataInicial'] : date('Y-m-01');
$dataFinal   = !empty($_GET['dataFinal'])   ? $_GET['dataFinal']   : date('Y-m-t');
$pago = $_GET['pago'] ?? '';
$tipo_data = $_GET['tipo_data'] ?? 'vencimento';

$dataInicialF = !empty($dataInicial) ? implode('/', array_reverse(explode('-', $dataInicial))) : '';
$dataFinalF = !empty($dataFinal) ? implode('/', array_reverse(explode('-', $dataFinal))) : '';

$texto_pago = '';
if ($pago === 'pagas') {
    $texto_pago = 'Contas Pagas';
} elseif ($pago === 'pendentes') {
    $texto_pago = 'Contas Pendentes';
} elseif ($pago === 'vencidas') {
    $texto_pago = 'Contas Vencidas';
} else {
    $texto_pago = 'Todas';
}

$texto_tipo_data = '';
if ($tipo_data === 'vencimento') {
    $texto_tipo_data = 'Data de Vencimento';
} elseif ($tipo_data === 'lancamento') {
    $texto_tipo_data = 'Data de Lançamento';
} elseif ($tipo_data === 'pagamento') {
    $texto_tipo_data = 'Data de Pagamento';
} else {
    $texto_tipo_data = 'Data de Vencimento';
}

// ✅ SE DATAS VAZIAS, USA MÊS ATUAL
if (empty($dataInicial) || empty($dataFinal)) {
    $dataInicial = date('Y-m-01');  // Primeiro dia do mês
    $dataFinal = date('Y-m-t');     // Último dia do mês
}

setlocale(LC_TIME, 'pt_BR', 'ptb', 'pt_BR.UTF-8');
$data_extenso = strftime('%A, %d de %B de %Y', strtotime(date('Y-m-d')));
$data_extenso = ucfirst($data_extenso);

$config = $pdo->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$nome_sistema = $config['nome_sistema'] ?? 'Sistema';
$endereco_sistema = $config['endereco_sistema'] ?? '';
$telefone_sistema = $config['telefone_sistema'] ?? '';
$instagram_sistema = $config['instagram_sistema'] ?? '';
$desenvolvedor = $config['desenvolvedor'] ?? '';
$site_dev = $config['site_dev'] ?? '';

// ✅ INICIALIZA TOTAIS ANTES (FORA DO TRY)
$total_pendentes    = 0;
$total_pago         = 0;
$total_vencidas     = 0;
$qtd_pendentes      = 0;
$qtd_pagas          = 0;
$qtd_vencidas      = 0;

$contas = [];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Contas a Pagar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #2c3e50;
            line-height: 1.4;
            padding: 20px;
        }

        .header {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .header-top {
            width: 100%;
            display: table;
            margin-bottom: 10px;
        }

        .header-logo {
            display: table-cell;
            width: 400px;
            vertical-align: middle;
        }

        .header-logo img {
            max-width: 200px;
            max-height: 80px;
        }

        .header-title {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            padding-right: 10px;
        }

        .header-title h1 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header-title p {
            font-size: 9px;
            color: #1b474a;
        }

        .header-date {
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
            font-style: italic;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #bdc3c7;
        }

        .filters {
            width: 100%;
            margin-bottom: 15px;
            background: #ecf0f1;
            padding: 10px;
            border-left: 4px solid #2c3e50;
            font-size: 9px;
        }

        .filters table {
            width: 100%;
            border: none;
        }

        .filters td {
            padding: 4px 6px;
            border: none;
        }

        .filters strong {
            color: #2c3e50;
            font-weight: bold;
            min-width: 100px;
            display: inline-block;
        }

        table.relatorio {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid #2c3e50;
        }

        table.relatorio thead th {
            background-color: #2c3e50;
            color: #ffffff !important;
            padding: 8px 5px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #2c3e50;
            font-weight: bold;
        }

        table.relatorio tbody td {
            padding: 6px 5px;
            border: 1px solid #d5dbdb;
            font-size: 9px;
        }

        table.relatorio tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .col-descricao {
            width: 25%;
        }

        .col-fornecedor {
            width: 20%;
        }

        .col-vencimento {
            width: 10%;
            text-align: center;
        }

        .col-pago {
            width: 10%;
            text-align: center;
        }

        .col-forma {
            width: 10%;
        }

        .col-valor {
            width: 12%;
            text-align: right;
            font-weight: bold;
        }

        .col-subtotal {
            width: 13%;
            text-align: right;
            font-weight: bold;
        }

        .status-pago {
            color: #27ae60;
            font-weight: bold;
            background-color: #d5f4e6;
            padding: 3px 8px;
            display: inline-block;
        }

        .status-pendente {
            color: #e74c3c;
            font-weight: bold;
            background-color: #fadbd8;
            padding: 3px 8px;
            display: inline-block;
        }

        table.relatorio tfoot td {
            background-color: #2c3e50;
            color: #ffffff !important;
            font-weight: bold;
            padding: 8px 5px;
            border: 1px solid #2c3e50;
            font-size: 10px;
        }

        .total-destaque {
            background-color: #27ae60 !important;
            font-size: 11px !important;
            text-align: right;
        }

        .footer-sistema {
            width: 100%;
            margin-top: 25px;
            padding: 15px 0;
            border-top: 2px solid #2c3e50;
            background-color: #f8f9fa;
            text-align: center;
            font-size: 8px;
            color: #555;
        }

        .footer-nome {
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .footer-endereco {
            font-size: 8px;
            color: #1b6e74;
            margin-bottom: 6px;
        }

        .footer-contato {
            margin: 5px 0;
            font-size: 8px;
        }

        .footer-contato a {
            color: #0ca44b;
            text-decoration: none;
            font-weight: bold;
            margin: 0 8px;
        }

        .footer-instagram {
            color: #E1306C !important;
        }

        .footer-dev {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #bdc3c7;
            font-size: 7px;
            color: #1e3536;
        }

        .footer-dev a {
            color: #1458d7;
            text-decoration: none;
            font-weight: bold;
        }

        .footer-data {
            margin-top: 5px;
            font-size: 7px;
            color: #14232d;
            font-style: italic;
        }

        @page {
            margin: 20mm;
        }

        #footer-paginacao {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #555;
        }

        .page-number:after {
            content: counter(page, decimal-leading-zero);
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="header">
            <div class="header-top">
                <div class="header-logo">
                    <img src="<?php echo realpath(__DIR__ . '/../../img/Logo.png'); ?>" alt="Logotipo">
                </div>
                <div class="header-title">
                    <h1>Relatório de Contas a Pagar</h1>
                    <p><?php echo $texto_pago; ?> • <?php echo $texto_tipo_data; ?></p>
                </div>
            </div>
            <div class="header-date">
                <strong><?php echo $data_extenso; ?></strong>
            </div>
        </div>
        <div class="filters">
            <table>
                <tr>
                    <td><strong>Período:</strong></td>
                    <td><?php echo !empty($dataInicialF) ? $dataInicialF : '...' ?> até <?php echo !empty($dataFinalF) ? $dataFinalF : '...' ?></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><?php echo $texto_pago; ?></td>
                </tr>
                <tr>
                    <td><strong>Tipo de Data:</strong></td>
                    <td><?php echo $texto_tipo_data; ?></td>
                </tr>
            </table>
        </div>
        <table class="relatorio">
            <thead>
                <tr>
                    <th class="col-descricao">Descrição</th>
                    <th class="col-fornecedor">Fornecedor</th>
                    <th class="col-vencimento">Vencimento</th>
                    <th class="col-pago">Pago em</th>
                    <th class="col-forma">Forma Pgto</th>
                    <th class="col-valor">Valor</th>
                    <th class="col-subtotal">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $coluna = match ($tipo_data) {
                        'lancamento' => 'data_lancamento',
                        'pagamento' => 'data_pagamento',
                        default => 'data_vencimento'
                    };

                    $sql = "SELECT r.*, f.nome as fornecedor_nome, fp.nome as forma_nome 
                            FROM pagar r
                            LEFT JOIN fornecedores f ON r.fornecedor = f.id
                            LEFT JOIN forma_pagamento fp ON r.forma_pagamento = fp.id
                            WHERE $coluna IS NOT NULL
                            AND $coluna BETWEEN :ini AND :fim";

                    if ($pago === 'pagas') {
                        $sql .= " AND r.data_pagamento IS NOT NULL";
                    } elseif ($pago === 'pendentes') {
                        $sql .= " AND r.data_pagamento IS NULL";
                    } elseif ($pago === 'vencidas') {
                        $sql .= " AND r.data_vencimento < :hoje AND r.data_pagamento IS NULL";
                    }

                    $dataInicial = !empty($dataInicial) ? $dataInicial : date('Y-m-01');
                    $dataFinal   = !empty($dataFinal)   ? $dataFinal   : date('Y-m-t');

                    $sql .= " ORDER BY r.id DESC";
                    $stmt = $pdo->prepare($sql);

                    if ($pago === 'vencidas') {
                        $stmt->execute([':ini' => $dataInicial, ':fim' => $dataFinal, ':hoje' => date('Y-m-d')]);
                    } else {
                        $stmt->execute([':ini' => $dataInicial, ':fim' => $dataFinal]);
                    }

                    $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $hoje = date('Y-m-d');
                    // ✅ CALCULA TOTAIS DENTRO DO TRY
                    foreach ($contas as $c) {
                        // ✅ Define valor base (prioriza subtotal)
                        $valorBase = (!empty($c['subtotal']) && $c['subtotal'] > 0) ? $c['subtotal'] : ($c['valor'] ?? 0);

                        $data_pagamento = $c['data_pagamento'] ?? null;
                        $data_vencimento = $c['data_vencimento'] ?? null;

                        // ✅ Classifica igual ao listar.php
                        if (!is_null($data_pagamento) && $data_pagamento != '' && $data_pagamento != '0000-00-00') {
                            // → PAGA
                            $total_pago += $valorBase;
                            $qtd_pagas++;
                        } elseif (!empty($data_vencimento) && $data_vencimento != '0000-00-00' && $data_vencimento < $hoje) {
                            // → VENCIDA
                            $total_vencidas += $valorBase;
                            $qtd_vencidas++;
                        } else {
                            // → PENDENTE
                            $total_pendentes += $valorBase;
                            $qtd_pendentes++;
                        }
                    }

                    $total_geral = $total_pago + $total_pendentes;
                } catch (Exception $e) {
                    echo '<tr><td colspan="7" style="text-align:center;color:#e74c3c;padding:20px;">Erro: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                    $total_geral = 0;
                }

                // ✅ EXIBE OS DADOS
                foreach ($contas as $c):
                    $pago_status = (!empty($c['data_pagamento']) && $c['data_pagamento'] != '0000-00-00') ? 'pago' : 'pendente';
                    $valorF = 'R$ ' . number_format($c['valor'] ?? 0, 2, ',', '.');
                    $subtotalF = 'R$ ' . number_format($c['subtotal'] ?? $c['valor'] ?? 0, 2, ',', '.');
                    $vencF = (!empty($c['data_vencimento']) && $c['data_vencimento'] != '0000-00-00') ? date('d/m/Y', strtotime($c['data_vencimento'])) : '-';
                    $pgtoF = (!empty($c['data_pagamento']) && $c['data_pagamento'] != '0000-00-00') ? date('d/m/Y', strtotime($c['data_pagamento'])) : 'Não pago';
                    $classe = ($pago_status === 'pago') ? 'status-pago' : 'status-pendente';
                ?>
                    <tr>
                        <td class="col-descricao"><?php echo htmlspecialchars($c['descricao'] ?? '') ?></td>
                        <td class="col-fornecedor"><?php echo htmlspecialchars($c['fornecedor_nome'] ?? '') ?></td>
                        <td class="col-vencimento" style="text-align: center;"><?php echo $vencF ?></td>
                        <td class="col-pago" style="text-align: center;"><span class="<?php echo $classe ?>"><?php echo $pgtoF ?></span></td>
                        <td class="col-forma"><?php echo htmlspecialchars($c['forma_nome'] ?? '') ?></td>
                        <td class="col-valor"><?php echo $valorF ?></td>
                        <td class="col-subtotal"><?php echo $subtotalF ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <!-- ✅ NOVA LINHA: Quantidades e Valores -->
                <tr>
                    <td colspan="7" style="text-align: right; font-size: 9px; padding: 4px 5px; background: #f8f9fa;">
                        <span style="color: #e74c3c; font-weight: bold;">Pendentes: <?php echo $qtd_pendentes; ?></span> |
                        <span style="color: #27ae60; font-weight: bold;">Pagas: <?php echo $qtd_pagas; ?></span> |
                        <span style="color: #ac2516; font-weight: bold;">Vencidas: <?php echo $qtd_vencidas; ?></span> |
                        <span style="color: #e74c3c; font-weight: bold;">Pendentes: R$ <?php echo number_format($total_pendentes, 2, ',', '.'); ?></span> |
                        <span style="color: #27ae60; font-weight: bold;">Pagas: R$ <?php echo number_format($total_pago, 2, ',', '.'); ?></span>
                        <span style="color: #ac2516; font-weight: bold;">Vencidas: R$ <?php echo number_format($total_vencidas, 2, ',', '.'); ?></span>
                    </td>
                </tr>

                <!-- ✅ LINHA EXISTENTE: Total Geral (mantida) -->
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: bold;">TOTAL GERAL:</td>
                    <td colspan="2" class="total-destaque" style="text-align: right; font-size: 12px; font-weight: bold;">
                        R$ <?php echo number_format($total_geral, 2, ',', '.'); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <div class="footer-sistema">
            <div class="footer-nome"><?php echo htmlspecialchars($nome_sistema); ?></div>
            <div class="footer-endereco"><?php echo htmlspecialchars($endereco_sistema); ?></div>
            <div class="footer-contato">
                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $telefone_sistema); ?>">📱 <?php echo htmlspecialchars($telefone_sistema); ?></a>
                <?php if (!empty($instagram_sistema)): ?>
                    <a href="<?php echo htmlspecialchars($instagram_sistema); ?>" class="footer-instagram" target="_blank">📷 Instagram</a>
                <?php endif; ?>
            </div>
            <div class="footer-dev">
                Desenvolvido por: <a href="<?php echo htmlspecialchars($site_dev ?: '#'); ?>" target="_blank"><?php echo htmlspecialchars($desenvolvedor ?: 'Sua Empresa'); ?></a>
            </div>
            <div class="footer-data">
                Relatório gerado em <?php echo date('d/m/Y \à\s H:i:s'); ?>
            </div>
        </div>
    </div>
    <div id="footer-paginacao">
        Página <span class="page-number"></span>
    </div>
</body>

</html>