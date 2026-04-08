<?php
require_once("../../conexao.php");

// ✅ Receber parâmetros com fallback seguro
$ano = isset($_GET['ano']) && !empty($_GET['ano']) ? intval($_GET['ano']) : date('Y');
$url_sistema = $_GET['url_sistema'] ?? '';
$nome_sistema = $_GET['nome_sistema'] ?? 'Sistema';
$telefone_sistema = $_GET['telefone_sistema'] ?? '';
$endereco_sistema = $_GET['endereco_sistema'] ?? '';

// ✅ DEBUG - Remova depois
error_log("Ano no php: " . $ano);

// ✅ Verifique se a data está sendo calculada corretamente
$data_ini = sprintf('%04d-%02d-01', $ano, 1); // Janeiro de 2025
$data_fim = date('Y-m-t', strtotime($data_ini)); // 2025-01-31

// ✅ DEBUG das datas
error_log("Data ini: " . $data_ini . " | Data fim: " . $data_fim);

// ... resto do código ...

// ✅ Meses para exibição
$meses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

// ✅ Inicializa totais anuais
$total_receitas_ano = 0;
$total_despesas_ano = 0;
$saldo_ano = 0;

// ✅ Array para armazenar dados mensais
$dados_mensais = [];

// ✅ Loop pelos 12 meses
for ($mes = 1; $mes <= 12; $mes++) {
    // ✅ Data inicial e final do mês
    $data_ini = sprintf('%04d-%02d-01', $ano, $mes);
    $data_fim = date('Y-m-t', strtotime($data_ini)); // Último dia do mês

    // ✅ Buscar RECEITAS (contas a receber PAGAS no período)
    $stmt_r = $pdo->prepare("
        SELECT COALESCE(SUM(subtotal), 0) as total 
        FROM receber 
        WHERE data_pagamento IS NOT NULL 
          AND data_pagamento != '0000-00-00'
          AND data_pagamento >= :ini 
          AND data_pagamento <= :fim
    ");
    $stmt_r->execute([':ini' => $data_ini, ':fim' => $data_fim]);
    $receitas = $stmt_r->fetchColumn();

    // ✅ Buscar DESPESAS (contas a pagar PAGAS no período)
    $stmt_d = $pdo->prepare("
        SELECT COALESCE(SUM(subtotal), 0) as total 
        FROM pagar 
        WHERE data_pagamento IS NOT NULL 
          AND data_pagamento != '0000-00-00'
          AND data_pagamento >= :ini 
          AND data_pagamento <= :fim
    ");
    $stmt_d->execute([':ini' => $data_ini, ':fim' => $data_fim]);
    $despesas = $stmt_d->fetchColumn();

    // ✅ Calcular saldo do mês
    $saldo = $receitas - $despesas;

    // ✅ Acumular totais anuais
    $total_receitas_ano += $receitas;
    $total_despesas_ano += $despesas;
    $saldo_ano += $saldo;

    // ✅ Armazenar dados do mês
    $dados_mensais[$mes] = [
        'nome' => $meses[$mes],
        'receitas' => $receitas,
        'despesas' => $despesas,
        'saldo' => $saldo
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Balanço Anual - <?php echo $ano; ?></title>
    <style>
        @page {
            margin: 20mm;
        }

        /* ← Aumentou de 15mm para 20mm */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #2c3e50;
            line-height: 1.5;
            /* ← Aumentou de 1.4 para 1.5 */
            padding: 5mm;
            /* ← Adicionado padding interno */
        }

        /* Cabeçalho */
        .header {
            width: 100%;
            margin-bottom: 20px;
            /* ← Aumentou de 15px */
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            /* ← Aumentou de 10px */
            text-align: center;
        }

        .header-logo {
            max-width: 150px;
            max-height: 50px;
            margin-bottom: 8px;
            /* ← Aumentou de 5px */
        }

        .header-nome {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
            /* ← Aumentou de 2px */
        }

        .header-endereco {
            font-size: 9px;
            color: #555;
        }

        /* Título do relatório */
        .relatorio-titulo {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0;
            /* ← Aumentou de 10px */
            color: #2c3e50;
        }

        .relatorio-subtitulo {
            text-align: center;
            font-size: 11px;
            color: #7f8c8d;
            margin-bottom: 20px;
            /* ← Aumentou de 15px */
        }

        /* Tabela */
        table.balanco {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            /* ← Aumentou de 15px */
        }

        table.balanco thead th {
            background-color: #2c3e50;
            color: #fff !important;
            padding: 10px 8px;
            /* ← Aumentou de 8px 5px */
            text-align: right;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #2c3e50;
        }

        table.balanco thead th:first-child {
            text-align: left;
        }

        table.balanco tbody td {
            padding: 8px 8px;
            /* ← Aumentou de 6px 5px */
            border: 1px solid #d5dbdb;
            font-size: 9px;
        }

        table.balanco tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table.balanco tbody td:first-child {
            text-align: left;
            font-weight: 600;
        }

        table.balanco tfoot td {
            background-color: #2c3e50;
            color: #fff !important;
            font-weight: bold;
            padding: 10px 8px;
            /* ← Aumentou de 8px 5px */
            border: 1px solid #2c3e50;
            font-size: 10px;
        }

        /* Colunas */
        .col-mes {
            width: 35%;
        }

        .col-valor {
            width: 22%;
            text-align: right !important;
        }

        .col-saldo {
            width: 21%;
            text-align: right !important;
            font-weight: bold;
        }

        /* Cores do saldo */
        .saldo-positivo {
            color: #27ae60 !important;
        }

        .saldo-negativo {
            color: #e74c3c !important;
        }

        .saldo-zero {
            color: #7f8c8d !important;
        }

        /* ✅ Adicione após as classes .saldo-positivo/.saldo-negativo */
        .receita-positivo {
            color: #27ae60 !important;
            font-weight: 600;
        }

        .despesa-positivo {
            color: #e74c3c !important;
            font-weight: 600;
        }

        /* Opcional: fundo suave para destacar */
        .receita-bg {
            background-color: rgba(39, 174, 96, 0.05) !important;
        }

        .despesa-bg {
            background-color: rgba(231, 76, 60, 0.05) !important;
        }

        .receita-positivo {
            background-color: #d5f4e6 !important;

            color: #27ae60 !important;
            font-weight: 600;
        }

        .despesa-positivo {
            background-color: #fadbd8 !important;

            color: #e74c3c !important;
            font-weight: 600;
        }

        /* ✅ Adicione no CSS, específico para o tfoot */
        table.balanco tfoot .receita-positivo {
            color: #06843a !important;
            /* Verde mais neon/brilhante */
            background-color: rgba(39, 174, 96, 0.3) !important;
        }

        table.balanco tfoot .despesa-positivo {
            color: #ec7063 !important;
            /* Vermelho mais neon/brilhante */
            background-color: rgba(231, 76, 60, 0.3) !important;
        }

        /* Resumo anual */
        .resumo-anual {
            margin-top: 25px;
            /* ← Aumentou de 20px */
            padding: 15px;
            /* ← Aumentou de 10px */
            background: #ecf0f1;
            border-left: 4px solid #2c3e50;
        }

        .resumo-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            /* ← Aumentou de 5px */
            font-size: 10px;
        }

        .resumo-label {
            font-weight: bold;
        }

        .resumo-valor {
            font-weight: bold;
        }

        .resumo-saldo {
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            margin-top: 12px;
            /* ← Aumentou de 8px */
            padding-top: 12px;
            /* ← Aumentou de 8px */
            border-top: 2px solid #2c3e50;
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

        /* Utilitários */
        .text-right {
            text-align: right !important;
        }

        .bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <!-- Cabeçalho -->
    <div class="header">
        <img class="header-logo" src="<?php echo $url_sistema; ?>img/Logo.png" alt="Logo">
        <div class="header-nome"><?php echo mb_strtoupper($nome_sistema); ?></div>
        <div class="header-endereco"><?php echo htmlspecialchars($endereco_sistema); ?> • 📞 <?php echo htmlspecialchars($telefone_sistema); ?></div>
    </div>

    <!-- Título -->
    <div class="relatorio-titulo">Balanço Anual</div>
    <div class="relatorio-subtitulo">Exercício: <?php echo $ano; ?> • Gerado em <?php echo date('d/m/Y H:i'); ?></div>

    <!-- Tabela Mensal -->
    <table class="balanco">
        <thead>
            <tr>
                <th class="col-mes">Mês</th>
                <th class="col-valor">Receitas</th>
                <th class="col-valor">Despesas</th>
                <th class="col-saldo">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados_mensais as $mes => $dados):
                $classe_saldo = $dados['saldo'] > 0 ? 'saldo-positivo' : ($dados['saldo'] < 0 ? 'saldo-negativo' : 'saldo-zero');

                // ✅ Classes para receitas e despesas
                $classe_receita = $dados['receitas'] > 0 ? 'receita-positivo' : '';
                $classe_despesa = $dados['despesas'] > 0 ? 'despesa-positivo' : '';
            ?>
                <tr>
                    <td class="col-mes"><?php echo ucfirst($dados['nome']); ?></td>
                    <td class="col-valor <?php echo $classe_receita; ?>">
                        R$ <?php echo number_format($dados['receitas'], 2, ',', '.'); ?>
                    </td>
                    <td class="col-valor <?php echo $classe_despesa; ?>">
                        R$ <?php echo number_format($dados['despesas'], 2, ',', '.'); ?>
                    </td>
                    <td class="col-saldo <?php echo $classe_saldo; ?>">
                        R$ <?php echo number_format($dados['saldo'], 2, ',', '.'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="bold">TOTAL ANUAL</td>
                <td class="text-right <?php echo $total_receitas_ano > 0 ? 'receita-positivo' : ''; ?>">
                    R$ <?php echo number_format($total_receitas_ano, 2, ',', '.'); ?>
                </td>
                <td class="text-right <?php echo $total_despesas_ano > 0 ? 'despesa-positivo' : ''; ?>">
                    R$ <?php echo number_format($total_despesas_ano, 2, ',', '.'); ?>
                </td>
                <td class="text-right bold <?php echo $saldo_ano >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">
                    R$ <?php echo number_format($saldo_ano, 2, ',', '.'); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Resumo Visual -->
    <div class="resumo-anual">
        <div class="resumo-item">
            <span class="resumo-label">Total Receitas:</span>
            <span class="resumo-valor text-right">R$ <?php echo number_format($total_receitas_ano, 2, ',', '.'); ?></span>
        </div>
        <div class="resumo-item">
            <span class="resumo-label">Total Despesas:</span>
            <span class="resumo-valor text-right">R$ <?php echo number_format($total_despesas_ano, 2, ',', '.'); ?></span>
        </div>
        <div class="resumo-saldo">
            SALDO FINAL:
            <span class="<?php echo $saldo_ano >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">
                R$ <?php echo number_format($saldo_ano, 2, ',', '.'); ?>
            </span>
        </div>
    </div>

    <!-- ✅ RODAPÉ PADRONIZADO (IGUAL AOS OUTROS RELATÓRIOS) -->
    <div class="footer-sistema">
        <div class="footer-nome"><?php echo htmlspecialchars($nome_sistema); ?></div>
        <div class="footer-endereco"><?php echo htmlspecialchars($endereco_sistema); ?></div>
        <div class="footer-contato">
            <!-- ✅ Telefone com ícone de telefone (caractere Unicode simples) -->
            <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $telefone_sistema); ?>">
                ☎ <?php echo htmlspecialchars($telefone_sistema); ?>
            </a>

            <!-- ✅ Instagram só aparece se tiver valor -->
            <?php if (!empty($instagram_sistema)): ?>
                <a href="<?php echo htmlspecialchars($instagram_sistema); ?>" class="footer-instagram" target="_blank">▣ Instagram</a>
            <?php endif; ?>
        </div>
        <div class="footer-dev">
            Desenvolvido por: <a href="<?php echo htmlspecialchars($site_dev ?: '#'); ?>" target="_blank">
                <?php echo htmlspecialchars($desenvolvedor ?: 'Sua Empresa'); ?>
            </a>
        </div>
        <div class="footer-data">
            Relatório gerado em <?php echo date('d/m/Y \à\s H:i'); ?>
        </div>
    </div>

    <!-- ✅ GRÁFICO DE BARRAS - COMPATÍVEL COM DOMPDF -->
    <div style="page-break-inside: avoid; margin-top: 30px; padding-top: 20px; border-top: 1px dashed #bdc3c7;">

        <h4 style="text-align: center; color: #2c3e50; margin-bottom: 15px; font-size: 12px; text-transform: uppercase;">
            ▣ Evolução Mensal do Saldo
        </h4>

        <!-- Container do gráfico -->
        <div style="display: table; width: 100%; height: 150px; border-bottom: 2px solid #2c3e50; border-left: 2px solid #2c3e50; padding: 10px 5px 5px 5px; margin-bottom: 5px;">

            <?php
            // ✅ Encontrar o maior valor absoluto para escala
            $max_valor = 1;
            foreach ($dados_mensais as $d) {
                $abs = abs($d['saldo']);
                if ($abs > $max_valor) $max_valor = $abs;
            }

            // ✅ Gerar barras
            foreach ($dados_mensais as $mes => $dados):
                $altura = $max_valor > 0 ? (abs($dados['saldo']) / $max_valor) * 130 : 0;
                $cor = $dados['saldo'] >= 0 ? '#27ae60' : '#e74c3c';
                $sigla = substr($dados['nome'], 0, 3);
            ?>
                <div style="display: table-cell; width: 8.33%; text-align: center; vertical-align: bottom; padding: 0 2px;">
                    <!-- Barra -->
                    <div style="
                width: 100%; 
                height: <?php echo max($altura, 2); ?>px; 
                background-color: <?php echo $cor; ?>; 
                border-radius: 2px 2px 0 0;
                margin-bottom: 3px;
                position: relative;
            ">
                        <!-- Valor no topo da barra (só se couber) -->
                        <?php if ($altura > 20): ?>
                            <span style="
                    position: absolute; 
                    top: -15px; 
                    left: 50%; 
                    transform: translateX(-50%); 
                    font-size: 7px; 
                    color: #2c3e50;
                    white-space: nowrap;
                ">
                                <?php echo number_format($dados['saldo'] / 1000, 1, ',', '.'); ?>k
                            </span>
                        <?php endif; ?>
                    </div>
                    <!-- Mês -->
                    <div style="font-size: 7px; color: #555; margin-top: 2px;"><?php echo $sigla; ?></div>
                </div>
            <?php endforeach; ?>

        </div>

        <!-- Legenda -->
        <div style="text-align: center; font-size: 8px; color: #7f8c8d;">
            <span style="color: #27ae60; font-weight: bold;">■ Saldo Positivo</span> &nbsp;&nbsp;
            <span style="color: #e74c3c; font-weight: bold;">■ Saldo Negativo</span> &nbsp;&nbsp;
            <small>Valores em R$ • Escala proporcional ao maior saldo do ano</small>
        </div>

    </div>

</body>

</html>