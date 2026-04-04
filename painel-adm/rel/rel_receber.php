<?php
require_once __DIR__ . '/../../conexao.php';
$dataInicial = $_GET['dataInicial'] ?? '';
$dataFinal = $_GET['dataFinal'] ?? '';
$pago = $_GET['pago'] ?? '';
$tipo_data = $_GET['tipo_data'] ?? 'vencimento';

$dataInicialF = !empty($dataInicial) ? implode('/', array_reverse(explode('-', $dataInicial))) : '';
$dataFinalF = !empty($dataFinal) ? implode('/', array_reverse(explode('-', $dataFinal))) : '';

$texto_pago = '';
if ($pago === 'pagas') {
    $texto_pago = 'Contas Pagas';
} elseif ($pago === 'pendentes') {
    $texto_pago = 'Contas Pendentes';
} else if ($pago === 'vencidas') {
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

// ✅ Data completa por extenso
setlocale(LC_TIME, 'pt_BR', 'ptb', 'pt_BR.UTF-8');
$data_extenso = strftime('%A, %d de %B de %Y', strtotime(date('Y-m-d')));
$data_extenso = ucfirst($data_extenso);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Contas a Receber</title>
    <style>
        /* ✅ RESET E BASE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #2c3e50;
            line-height: 1.5;
            background: #ffffff;
        }

        /* ✅ CABEÇALHO PRINCIPAL */
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
        }

        .header-top {
            width: 100%;
            display: table;
            margin-bottom: 10px;
        }

        .header-logo {
            display: table-cell;
            width: 180px;
            vertical-align: middle;
        }

        .header-logo img {
            max-width: 180px;
            max-height: 70px;
            object-fit: contain;
        }

        .header-title {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            padding-right: 10px;
        }

        .header-title h1 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: bold;
        }

        .header-title p {
            font-size: 10px;
            color: #7f8c8d;
            font-style: italic;
        }

        .header-date {
            text-align: center;
            font-size: 9px;
            color: #424a44;
            font-style: italic;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #bdc3c7;
        }

        .marca {
            position: fixed;
            left: 50;
            top: 100;
            width: 80%;
            opacity: 8%;
        }

        /* ✅ BOX DE FILTROS */
        .filters-box {
            width: 100%;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #ecf0f1 0%, #d5dbdb 100%);
            padding: 12px;
            border-left: 4px solid #2c3e50;
            border-radius: 0 5px 5px 0;
        }

        .filters-box table {
            width: 100%;
            border: none;
        }

        .filters-box td {
            padding: 4px 8px;
            border: none;
            font-size: 9px;
        }

        .filters-box strong {
            color: #2c3e50;
            font-weight: bold;
            min-width: 100px;
            display: inline-block;
        }

        .filters-box span {
            color: #34495e;
        }

        /* ✅ TABELA PRINCIPAL */
        table.relatorio {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table.relatorio thead th {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ffffff;
            padding: 10px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border: 1px solid #2c3e50;
            font-weight: bold;
        }

        table.relatorio thead th:first-child {
            border-top-left-radius: 5px;
        }

        table.relatorio thead th:last-child {
            border-top-right-radius: 5px;
        }

        table.relatorio tbody td {
            padding: 8px 6px;
            border: 1px solid #d5dbdb;
            font-size: 9px;
            vertical-align: middle;
        }

        table.relatorio tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        table.relatorio tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        /* ✅ COLUNAS ESPECÍFICAS */
        .col-descricao {
            width: 28%;
        }

        .col-paciente {
            width: 22%;
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
            width: 10%;
            text-align: right;
        }

        .col-subtotal {
            width: 10%;
            text-align: right;
            font-weight: bold;
        }

        /* ✅ STATUS */
        .status-pago {
            color: #27ae60;
            font-weight: bold;
            background: #d5f4e6;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
        }

        .status-pendente {
            color: #e74c3c;
            font-weight: bold;
            background: #fadbd8;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
        }

        .status-vencido {
            color: #e67e22;
            font-weight: bold;
            background: #fdebd0;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
        }

        /* ✅ RODAPÉ DA TABELA (TOTAIS) */
        table.relatorio tfoot td {
            background: #2c3e50;
            color: #ffffff;
            font-weight: bold;
            padding: 10px 6px;
            border: 1px solid #2c3e50;
            font-size: 10px;
        }

        table.relatorio tfoot td:first-child {
            border-bottom-left-radius: 5px;
        }

        table.relatorio tfoot td:last-child {
            border-bottom-right-radius: 5px;
        }

        .total-label {
            text-align: right;
            background: #34495e !important;
        }

        .total-valor {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%) !important;
            font-size: 12px !important;
            text-align: right;
            padding: 12px 6px !important;
        }

        /* ✅ RODAPÉ DO SISTEMA */
        .footer-sistema {
            width: 100%;
            margin-top: 30px;
            padding: 15px;
            border-top: 3px solid #2c3e50;
            background: linear-gradient(135deg, #f8f9fa 0%, #ecf0f1 100%);
            text-align: center;
            page-break-inside: avoid;
        }

        .footer-nome {
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-endereco {
            font-size: 9px;
            color: #454c63;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .footer-contato {
            margin: 8px 0;
            font-size: 9px;
        }

        .footer-contato a {
            color: #27ae60;
            text-decoration: none;
            font-weight: bold;
            margin: 0 10px;
        }

        .footer-instagram {
            color: #E1306C !important;
        }


        .footer-labelDev {
            color: #11204f;
            font-size: 8px;
        }

        .footer-dev {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #bdc3c7;
            font-size: 8px;
            color: #4d5f94;
        }

        .footer-dev a {
            color: #306ee1;
            text-decoration: none;
            font-weight: bold;
        }

        .footer-data {
            margin-top: 8px;
            font-size: 7px;
            color: #344037;
            font-style: italic;
        }

        /* ✅ UTILITÁRIOS */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-muted {
            color: #95a5a6;
        }

        /* ✅ VALORES MONETÁRIOS */
        .valor {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .valor-positivo {
            color: #27ae60;
        }

        .valor-negativo {
            color: #e74c3c;
        }
    </style>
</head>

<body>
    <?php
        if ($marca_dagua == 'Sim') { ?>
            <img class="marca" src="<?php echo $url_sistema ?>img/logo.png" alt="Marca D'Água">
        <?php } ?>
    ?>
    <!-- Cabeçalho -->
    <div class="header">
        <div class="header-top">
            <div class="header-logo">
                <img src="..." alt="Logo">
            </div>
            <div class="header-title">
                <h1>Relatório de Contas a Receber</h1>
                <p>Contas Pagas • Data de Lançamento</p>
            </div>
        </div>
        <div class="header-date">
            Gerado em: Sábado, 04 de abril de 2026
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-box">
        <table>
            <tr>
                <td><strong>Período:</strong></td>
                <td><span>01/04/2026 até 30/04/2026</span></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td><span>Contas Pagas</span></td>
            </tr>
            <tr>
                <td><strong>Tipo de Data:</strong></td>
                <td><span>Data de Lançamento</span></td>
            </tr>
        </table>
    </div>

    <!-- Tabela -->
    <table class="relatorio">
        <thead>
            <tr>
                <th class="col-descricao">Descrição</th>
                <th class="col-paciente">Paciente</th>
                <th class="col-vencimento">Vencimento</th>
                <th class="col-pago">Pago em</th>
                <th class="col-forma">Forma Pgto</th>
                <th class="col-valor">Valor</th>
                <th class="col-subtotal">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <!-- Seus dados aqui -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="total-label">TOTAL GERAL:</td>
                <td colspan="2" class="total-valor">R$ 0,00</td>
            </tr>
        </tfoot>
    </table>

    <!-- Rodapé -->
    <div class="footer-sistema">
        <div class="footer-nome">FG Odontologia e Estética</div>
        <div class="footer-endereco">Rua São José, 20 - Vila Júlia - Mogi Guaçu/SP</div>
        <div class="footer-contato">
            <a href="tel:...">📱 (19) 38916-797</a>
            <a href="..." class="footer-instagram">📷 Instagram</a>
        </div>
        <div class="footer-dev">
            <span class="footer-labelDev">Desenvolvido por: </span><a href="https://vetor256.com">Vetor256</a>
        </div>
        <div class="footer-data">
            Relatório gerado em 04/04/2026 às 09:44:55
        </div>
    </div>
</body>

</html>