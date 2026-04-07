<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../../conexao.php';
}
// rel_fin.php - Visual do relatório financeiro (PADRONIZADO)
// Recebe variáveis via $_GET do rel_fin_class.php

// ✅ Formata datas para exibição (se não vierem formatadas do class)
$dataInicialF = $_GET['dataInicialF'] ?? (!empty($_GET['dataInicial']) ? implode('/', array_reverse(explode('-', $_GET['dataInicial']))) : '');
$dataFinalF = $_GET['dataFinalF'] ?? (!empty($_GET['dataFinal']) ? implode('/', array_reverse(explode('-', $_GET['dataFinal']))) : '');

// ✅ Data por extenso (compatível com PHP 8.1+)
if (!isset($_GET['data_extenso'])) {
    $meses = [
        'January' => 'janeiro',
        'February' => 'fevereiro',
        'March' => 'março',
        'April' => 'abril',
        'May' => 'maio',
        'June' => 'junho',
        'July' => 'julho',
        'August' => 'agosto',
        'September' => 'setembro',
        'October' => 'outubro',
        'November' => 'novembro',
        'December' => 'dezembro'
    ];
    $dias = [
        'Monday' => 'Segunda-feira',
        'Tuesday' => 'Terça-feira',
        'Wednesday' => 'Quarta-feira',
        'Thursday' => 'Quinta-feira',
        'Friday' => 'Sexta-feira',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];

    $data_en = date('l, j \d\e F \d\e Y', strtotime(date('Y-m-d')));
    $data_pt = strtr($data_en, $meses + $dias);
    $_GET['data_extenso'] = ucfirst($data_pt);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Relatório Sintético</title>
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
            color: <?php echo $_GET['cor_destaque']; ?>;
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
            border-left: 4px solid <?php echo $_GET['cor_destaque']; ?>;
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
            min-width: 120px;
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
            background-color: <?php echo $_GET['cor_destaque']; ?> !important;
            font-size: 11px !important;
            text-align: right;
        }

        .resumo {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .resumo td {
            padding: 8px;
            border: 1px solid #d5dbdb;
            text-align: center;
            font-size: 9px;
        }

        .resumo .titulo {
            background-color: #ecf0f1;
            font-weight: bold;
            color: #2c3e50;
        }

        .resumo .valor {
            font-weight: bold;
            font-size: 10px;
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
                    <img src="<?php echo $url_sistema . '/img/Logo.png'; ?>" alt="Logotipo">
                </div>
                <div class="header-title">
                    <h1>Relatório Sintético</h1>
                    <p><?php echo $_GET['filtro_tipo']; ?></p>
                </div>
            </div>
            <!-- ✅ DATA POR EXTENSO (PADRÃO DOS OUTROS RELATÓRIOS) -->
            <div class="header-date">
                <strong><?php echo $_GET['data_extenso']; ?></strong>
            </div>
        </div>

        <!-- ✅ RESUMO COM TOTAIS -->
        <table class="resumo">
            <tr>
                <td class="titulo">Total Geral</td>
                <td class="titulo">Total Pago</td>
                <td class="titulo">Total Pendente</td>
                <td class="titulo">Total Vencidas</td>
            </tr>
            <tr>
                <td class="valor" style="color: #2c3e50;">
                    R$ <?php echo number_format($_GET['total_geral'], 2, ',', '.'); ?>
                </td>
                <td class="valor" style="color: #27ae60;">
                    R$ <?php echo number_format($_GET['total_pago'], 2, ',', '.'); ?>
                </td>
                <td class="valor" style="color: #e74c3c;">
                    R$ <?php echo number_format($_GET['total_pendente'], 2, ',', '.'); ?>
                </td>
                <td class="valor" style="color: #8a2116;">
                    R$ <?php echo number_format($_GET['total_vencidas'], 2, ',', '.'); ?>
                </td>
            </tr>
            <tr>
                <td class="valor">
                    <?php echo $_GET['qtd_pago'] + $_GET['qtd_pendente']; ?> registros
                </td>
                <td class="valor">
                    <?php echo $_GET['qtd_pago']; ?> pagos
                </td>
                <td class="valor">
                    <?php echo $_GET['qtd_pendente']; ?> pendentes
                </td>
                <td class="valor">
                    <?php echo $_GET['qtd_vencidas']; ?> vencidas
                </td>
            </tr>
        </table>

        <!-- ✅ FILTROS -->
        <div class="filters">
            <table>
                <tr>
                    <td><strong>Período:</strong></td>
                    <td><?php echo $dataInicialF; ?> até <?php echo $dataFinalF; ?></td>
                </tr>
                <tr>
                    <td><strong>Tipo de Data:</strong></td>
                    <td><?php echo $_GET['filtro_data']; ?></td>
                </tr>
                <tr>
                    <td><strong>Tipo Lançamento:</strong></td>
                    <td><?php echo $_GET['filtro_lancamento']; ?></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><?php echo $_GET['filtro_pendente']; ?></td>
                </tr>
            </table>
        </div>

        <!-- ✅ TABELA DE DADOS -->
        <table class="relatorio">
            <thead>
                <tr>
                    <th style="width: 12%;">Data</th>
                    <th style="width: 35%;">Descrição</th>
                    <th style="width: 25%;"><?php echo $_GET['filtro_tipo'] === 'Saídas / Despesas' ? 'Fornecedor' : 'Paciente'; ?></th>
                    <th style="width: 13%;">Forma Pgto</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 15%; text-align: right;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($_GET['contas'])): ?>
                    <?php foreach ($_GET['contas'] as $c): ?>
                        <?php
                        $pago_status = (!empty($c['data_pagamento']) && $c['data_pagamento'] != '0000-00-00') ? 'pago' : 'pendente';
                        $classe = ($pago_status === 'pago') ? 'status-pago' : 'status-pendente';
                        $status_txt = ($pago_status === 'pago') ? 'Pago' : 'Pendente';
                        $dataF = (!empty($c['data_vencimento']) && $c['data_vencimento'] != '0000-00-00')
                            ? date('d/m/Y', strtotime($c['data_vencimento']))
                            : '-';
                        $valorF = 'R$ ' . number_format($c['subtotal'] ?? $c['valor'] ?? 0, 2, ',', '.');
                        ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $dataF; ?></td>
                            <td><?php echo htmlspecialchars($c['descricao'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($c['pessoa_nome'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($c['forma_nome'] ?? '-'); ?></td>
                            <td style="text-align: center;"><span class="<?php echo $classe; ?>"><?php echo $status_txt; ?></span></td>
                            <td style="text-align: right; font-weight: bold;"><?php echo $valorF; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #7f8c8d;">
                            Nenhum registro encontrado no período selecionado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right;">TOTAL GERAL:</td>
                    <td class="total-destaque">
                        R$ <?php echo number_format($_GET['total_geral'], 2, ',', '.'); ?>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- ✅ RODAPÉ PADRONIZADO (IGUAL AOS OUTROS RELATÓRIOS) -->
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