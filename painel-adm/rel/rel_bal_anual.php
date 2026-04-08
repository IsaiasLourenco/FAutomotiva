<?php
// ✅ NÃO fazer require de conexao aqui (já vem do class)

$ano = $ano ?? date('Y');
$url_sistema = $url_sistema ?? '';
$nome_sistema = $nome_sistema ?? 'Sistema';
$telefone_sistema = $telefone_sistema ?? '';
$endereco_sistema = $endereco_sistema ?? '';
$instagram_sistema = $instagram_sistema ?? '';
$site_dev = $site_dev ?? '';
$desenvolvedor = $desenvolvedor ?? '';

$meses = [1 => 'Janeiro', 
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
          12 => 'Dezembro'];

$total_receitas_ano = 0;
$total_despesas_ano = 0;
$saldo_ano = 0;
$dados_mensais = [];

for ($mes = 1; $mes <= 12; $mes++) {
    $data_ini = sprintf('%04d-%02d-01', $ano, $mes);
    $data_fim = date('Y-m-t', strtotime($data_ini));

    // ✅ RECEITAS - Query SAFE para MySQL strict mode
    $stmt_r = $pdo->prepare("SELECT COALESCE(SUM(subtotal),0) as total FROM receber 
        WHERE data_pagamento IS NOT NULL 
          AND YEAR(data_pagamento) > 1000 
          AND data_pagamento >= :ini 
          AND data_pagamento <= :fim");
    $stmt_r->execute([':ini' => $data_ini, ':fim' => $data_fim]);
    $receitas = $stmt_r->fetchColumn() ?: 0;

    // ✅ DESPESAS - Query SAFE para MySQL strict mode
    $stmt_d = $pdo->prepare("SELECT COALESCE(SUM(subtotal),0) as total FROM pagar 
        WHERE data_pagamento IS NOT NULL 
          AND YEAR(data_pagamento) > 1000 
          AND data_pagamento >= :ini 
          AND data_pagamento <= :fim");
    $stmt_d->execute([':ini' => $data_ini, ':fim' => $data_fim]);
    $despesas = $stmt_d->fetchColumn() ?: 0;

    $saldo = $receitas - $despesas;
    $total_receitas_ano += $receitas;
    $total_despesas_ano += $despesas;
    $saldo_ano += $saldo;
    $dados_mensais[$mes] = ['nome' => $meses[$mes], 'receitas' => $receitas, 'despesas' => $despesas, 'saldo' => $saldo];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Balanço Anual - <?php echo $ano; ?></title>
    <style>
        @page {
            margin: 20mm
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #2c3e50;
            line-height: 1.5;
            padding: 5mm
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            text-align: center
        }

        .header-logo {
            max-width: 150px;
            max-height: 50px;
            margin-bottom: 8px
        }

        .header-nome {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px
        }

        .header-endereco {
            font-size: 9px;
            color: #555
        }

        .relatorio-titulo {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0;
            color: #2c3e50
        }

        .relatorio-subtitulo {
            text-align: center;
            font-size: 11px;
            color: #7f8c8d;
            margin-bottom: 20px
        }

        table.balanco {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px
        }

        table.balanco thead th {
            background: #2c3e50;
            color: #fff !important;
            padding: 10px 8px;
            text-align: right;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #2c3e50
        }

        table.balanco thead th:first-child {
            text-align: left
        }

        table.balanco tbody td {
            padding: 8px 8px;
            border: 1px solid #d5dbdb;
            font-size: 9px
        }

        table.balanco tbody tr:nth-child(even) {
            background: #f8f9fa
        }

        table.balanco tbody td:first-child {
            text-align: left;
            font-weight: 600
        }

        table.balanco tfoot td {
            background: #2c3e50;
            color: #fff !important;
            font-weight: bold;
            padding: 10px 8px;
            border: 1px solid #2c3e50;
            font-size: 10px
        }

        .col-mes {
            width: 35%
        }

        .col-valor {
            width: 22%;
            text-align: right !important
        }

        .col-saldo {
            width: 21%;
            text-align: right !important;
            font-weight: bold
        }

        .saldo-positivo {
            color: #27ae60 !important
        }

        .saldo-negativo {
            color: #e74c3c !important
        }

        .saldo-zero {
            color: #7f8c8d !important
        }

        .receita-positivo {
            color: #27ae60 !important;
            font-weight: 600;
            background: #d5f4e6 !important
        }

        .despesa-positivo {
            color: #e74c3c !important;
            font-weight: 600;
            background: #fadbd8 !important
        }

        table.balanco tfoot .receita-positivo,
        table.balanco tfoot .despesa-positivo {
            color: #fff !important;
            background: none !important
        }

        .resumo-anual {
            margin-top: 25px;
            padding: 15px;
            background: #ecf0f1;
            border-left: 4px solid #2c3e50
        }

        .resumo-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 10px
        }

        .resumo-label {
            font-weight: bold
        }

        .resumo-valor {
            font-weight: bold
        }

        .resumo-saldo {
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 2px solid #2c3e50
        }

        .footer-sistema {
            width: 100%;
            margin-top: 25px;
            padding: 15px 0;
            border-top: 2px solid #2c3e50;
            background: #f8f9fa;
            text-align: center;
            font-size: 8px;
            color: #555
        }

        .footer-nome {
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 4px;
            text-transform: uppercase
        }

        .footer-endereco {
            font-size: 8px;
            color: #1b6e74;
            margin-bottom: 6px
        }

        .footer-contato {
            margin: 5px 0;
            font-size: 8px
        }

        .footer-contato a {
            color: #0ca44b;
            text-decoration: none;
            font-weight: bold;
            margin: 0 8px
        }

        .footer-instagram {
            color: #E1306C !important
        }

        .footer-dev {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #bdc3c7;
            font-size: 7px;
            color: #1e3536
        }

        .footer-dev a {
            color: #1458d7;
            text-decoration: none;
            font-weight: bold
        }

        .footer-data {
            margin-top: 5px;
            font-size: 7px;
            color: #14232d;
            font-style: italic
        }

        .text-right {
            text-align: right !important
        }

        .bold {
            font-weight: bold
        }

        .uppercase {
            text-transform: uppercase
        }
    </style>
</head>

<body>
    <div class="header">
        <img class="header-logo" src="<?php echo $url_sistema?>/img/Logo.png" alt="Logo">
        <div class="header-nome"><?php echo mb_strtoupper($nome_sistema); ?></div>
        <div class="header-endereco"><?php echo htmlspecialchars($endereco_sistema); ?> • ☎ <?php echo htmlspecialchars($telefone_sistema); ?></div>
    </div>
    <div class="relatorio-titulo">Balanço Anual</div>
    <div class="relatorio-subtitulo">Exercício: <?php echo $ano; ?> • Gerado em <?php echo date('d/m/Y H:i'); ?></div>
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
            <?php foreach ($dados_mensais as $dados):
                $cs = $dados['saldo'] > 0 ? 'saldo-positivo' : ($dados['saldo'] < 0 ? 'saldo-negativo' : 'saldo-zero');
                $cr = $dados['receitas'] > 0 ? 'receita-positivo' : '';
                $cd = $dados['despesas'] > 0 ? 'despesa-positivo' : '';
            ?>
                <tr>
                    <td class="col-mes"><?php echo ucfirst($dados['nome']); ?></td>
                    <td class="col-valor <?php echo $cr; ?>">R$ <?php echo number_format($dados['receitas'], 2, ',', '.'); ?></td>
                    <td class="col-valor <?php echo $cd; ?>">R$ <?php echo number_format($dados['despesas'], 2, ',', '.'); ?></td>
                    <td class="col-saldo <?php echo $cs; ?>">R$ <?php echo number_format($dados['saldo'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="bold">TOTAL ANUAL</td>
                <td class="text-right">R$ <?php echo number_format($total_receitas_ano, 2, ',', '.'); ?></td>
                <td class="text-right">R$ <?php echo number_format($total_despesas_ano, 2, ',', '.'); ?></td>
                <td class="text-right bold 
                    <?php echo $saldo_ano >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">R$ 
                    <?php echo number_format($saldo_ano, 2, ',', '.'); ?>
                </td>
            </tr>
        </tfoot>
    </table>
    <div class="resumo-anual">
        <div class="resumo-item">
            <span class="resumo-label">Total Receitas:</span>
            <span class="resumo-valor text-right">R$ <?php echo number_format($total_receitas_ano, 2, ',', '.'); ?></span>
        </div>
        <div class="resumo-item">
            <span class="resumo-label">Total Despesas:</span>
            <span class="resumo-valor text-right">R$ <?php echo number_format($total_despesas_ano, 2, ',', '.'); ?></span></div>
        <div class="resumo-saldo">SALDO FINAL: 
            <span class="<?php echo $saldo_ano >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">R$ 
                <?php echo number_format($saldo_ano, 2, ',', '.'); ?>
            </span>
        </div>
    </div>
    <div class="footer-sistema">
        <div class="footer-nome"><?php echo htmlspecialchars($nome_sistema); ?></div>
        <div class="footer-endereco"><?php echo htmlspecialchars($endereco_sistema); ?></div>
        <div class="footer-contato">
            <a href="tel:
                <?php echo preg_replace('/[^0-9]/', '', $telefone_sistema); ?>">
                ☎ <?php echo htmlspecialchars($telefone_sistema); ?>
            </a>
            <?php if (!empty($instagram_sistema)): ?>
                <a href="<?php echo htmlspecialchars($instagram_sistema); ?>" class="footer-instagram" target="_blank">
                    ▣ Instagram
                </a><?php endif; ?>
        </div>
        <div class="footer-dev">Desenvolvido por: 
            <a href="<?php echo htmlspecialchars($site_dev ?: '#'); ?>" target="_blank">
                <?php echo htmlspecialchars($desenvolvedor ?: 'Sua Empresa'); ?></a></div>
        <div class="footer-data">Relatório gerado em <?php echo date('d/m/Y \à\s H:i'); ?></div>
    </div>
    <div style="page-break-inside:avoid;margin-top:30px;padding-top:20px;border-top:1px dashed #bdc3c7">
        <h4 style="text-align:center;color:#2c3e50;margin-bottom:15px;font-size:12px;text-transform:uppercase">
            ▣ Evolução Mensal do Saldo
        </h4>
        <div style="display:table;
                    width:100%;
                    height:150px;
                    border-bottom:2px solid #2c3e50;
                    border-left:2px solid #2c3e50;
                    padding:10px 5px 5px 5px;
                    margin-bottom:5px">
            <?php $mv = 1;
            foreach ($dados_mensais as $d) {
                if (abs($d['saldo']) > $mv) $mv = abs($d['saldo']);
            } ?>
            <?php foreach ($dados_mensais as $dados): $alt = $mv > 0 ? (abs($dados['saldo']) / $mv) * 130 : 0;
                $cor = $dados['saldo'] >= 0 ? '#27ae60' : '#e74c3c';
                $sig = substr($dados['nome'], 0, 3); ?>
                <div style="display:table-cell;
                            width:8.33%;
                            text-align:center;
                            vertical-align:bottom;
                            padding:0 2px">
                    <div style="width:100%;height:
                        <?php echo max($alt, 2); ?>px;background:
                        <?php echo $cor; ?>;border-radius:2px 2px 0 0;margin-bottom:3px">
                    </div>
                    <div style="font-size:7px;color:#555;margin-top:2px"><?php echo $sig; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;
                    font-size:8px;
                    color:#7f8c8d">
                    <span style="color:#27ae60;
                                 font-weight:bold">
                        ■ Positivo
                    </span> &nbsp; 
                    <span style="color:#e74c3c;font-weight:bold">
                        ■ Negativo
                    </span>
        </div>
    </div>
</body>

</html>