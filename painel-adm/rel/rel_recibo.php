<?php
require_once("../../conexao.php");

// ✅ Receber parâmetros
$id = $_GET['id'] ?? 0;
$url_sistema = $_GET['url_sistema'] ?? '';
$nome_sistema = $_GET['nome_sistema'] ?? 'Sistema';
$telefone_sistema = $_GET['telefone_sistema'] ?? '';
$endereco_sistema = $_GET['endereco_sistema'] ?? '';

// ✅ Buscar dados da conta (prepared statement para segurança)
$stmt = $pdo->prepare("SELECT * FROM receber WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$conta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$conta) {
    echo '<p style="text-align:center;padding:20px;">Conta não encontrada.</p>';
    exit;
}

// ✅ Extrair campos com fallback seguro
$descricao       = htmlspecialchars($conta['descricao'] ?? '');
$paciente_id     = $conta['paciente'] ?? 0;
$valor           = $conta['valor'] ?? 0;
$subtotal        = $conta['subtotal'] ?? $valor;
$data_pagamento  = $conta['data_pagamento'] ?? '';
$data_vencimento = $conta['data_vencimento'] ?? '';
$forma_pgto_id   = $conta['forma_pagamento'] ?? 0;
$obs             = htmlspecialchars($conta['obs'] ?? '');

// ✅ Buscar nome do paciente
$nome_paciente = 'Não informado';
if ($paciente_id) {
    $stmt_p = $pdo->prepare("SELECT nome FROM clientes WHERE id = :id LIMIT 1");
    $stmt_p->execute([':id' => $paciente_id]);
    $pac = $stmt_p->fetch(PDO::FETCH_ASSOC);
    $nome_paciente = $pac['nome'] ?? 'Não informado';
}

// ✅ Buscar nome da forma de pagamento
$nome_forma_pgto = 'Não informado';
if ($forma_pgto_id) {
    $stmt_f = $pdo->prepare("SELECT nome FROM forma_pagamento WHERE id = :id LIMIT 1");
    $stmt_f->execute([':id' => $forma_pgto_id]);
    $fp = $stmt_f->fetch(PDO::FETCH_ASSOC);
    $nome_forma_pgto = $fp['nome'] ?? 'Não informado';
}

// ✅ Formatações para exibição
$valorF = 'R$ ' . number_format($subtotal, 2, ',', '.');
$data_pagamentoF = !empty($data_pagamento) && $data_pagamento != '0000-00-00'
    ? date('d/m/Y H:i', strtotime($data_pagamento))
    : date('d/m/Y');
$numero_recibo = 'REC-' . str_pad($id, 6, '0', STR_PAD_LEFT) . '/' . date('Y');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Recibo de Pagamento</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
            padding: 5mm;
            width: 70mm;
            margin: 0 auto;
        }

        /* Cabeçalho */
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .header-logo {
            max-width: 60mm;
            max-height: 15mm;
            margin-bottom: 2mm;
        }

        .header-nome {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 1mm;
        }

        .header-endereco {
            font-size: 8px;
            color: #333;
            margin-bottom: 0.5mm;
        }

        .header-telefone {
            font-size: 8px;
            color: #333;
        }

        /* Título do recibo */
        .recibo-titulo {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 3mm 0;
            border-bottom: 2px solid #000;
            padding-bottom: 2mm;
        }

        /* Informações principais */
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2mm;
        }

        .info-label {
            font-weight: bold;
        }

        .info-value {
            text-align: right;
        }

        /* Dados do pagamento */
        .pagamento-box {
            border: 1px solid #000;
            padding: 3mm;
            margin: 3mm 0;
            background: #f9f9f9;
        }

        .pagamento-titulo {
            font-weight: bold;
            text-align: center;
            margin-bottom: 2mm;
            text-transform: uppercase;
            font-size: 11px;
        }

        .pagamento-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .pagamento-destaque {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin: 2mm 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
        }

        /* Texto do recibo */
        .texto-recibo {
            text-align: justify;
            margin: 3mm 0;
            line-height: 1.5;
        }

        .texto-recibo b {
            font-weight: bold;
        }

        /* Assinatura */
        .assinatura-box {
            text-align: center;
            margin: 5mm 0 2mm 0;
        }

        .assinatura-linha {
            border-top: 1px solid #000;
            width: 90%;
            margin: 0 auto 1mm auto;
            padding-top: 0.5mm;
        }

        .assinatura-texto {
            font-size: 8px;
            text-transform: uppercase;
        }

        /* Rodapé */
        .footer {
            text-align: center;
            font-size: 8px;
            margin-top: 3mm;
            border-top: 1px dashed #000;
            padding-top: 2mm;
        }

        .footer-obs {
            font-size: 7px;
            color: #555;
            margin-top: 1mm;
            font-style: italic;
        }

        /* Utilitários */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .mb-1 {
            margin-bottom: 1mm;
        }

        .mb-2 {
            margin-bottom: 2mm;
        }

        .mt-1 {
            margin-top: 1mm;
        }

        .mt-2 {
            margin-top: 2mm;
        }
    </style>
</head>

<body>

    <!-- Cabeçalho -->
    <div class="header">
        <img class="header-logo" src="<?php echo $url_sistema?>/img/Logo.png" alt="Logo">
        <div class="header-nome"><?php echo mb_strtoupper($nome_sistema); ?></div>
        <div class="header-endereco"><?php echo htmlspecialchars($endereco_sistema); ?></div>
        <div class="header-telefone">☎ <?php echo htmlspecialchars($telefone_sistema); ?></div>
    </div>

    <!-- Título -->
    <div class="recibo-titulo">Recibo de Pagamento</div>

    <!-- Número e Data -->
    <div class="info-row mb-1">
        <span class="info-label">Nº:</span>
        <span class="info-value bold"><?php echo $numero_recibo; ?></span>
    </div>
    <div class="info-row mb-2">
        <span class="info-label">Data:</span>
        <span class="info-value"><?php echo $data_pagamentoF; ?></span>
    </div>

    <!-- Dados do Pagamento -->
    <div class="pagamento-box">
        <div class="pagamento-titulo">Detalhes do Pagamento</div>

        <div class="pagamento-item">
            <span class="info-label">Cliente:</span>
            <span class="info-value"><?php echo $nome_paciente; ?></span>
        </div>
        <div class="pagamento-item">
            <span class="info-label">Descrição:</span>
            <span class="info-value text-right"><?php echo $descricao; ?></span>
        </div>
        <div class="pagamento-item">
            <span class="info-label">Forma Pgto:</span>
            <span class="info-value"><?php echo $nome_forma_pgto; ?></span>
        </div>

        <div class="pagamento-destaque">
            TOTAL: <?php echo $valorF; ?>
        </div>
    </div>

    <!-- Texto do Recibo -->
    <div class="texto-recibo">
        Declaro(amos) ter recebido de <b><?php echo $nome_paciente; ?></b> a quantia de <b><?php echo $valorF; ?></b> referente a <b><?php echo $descricao; ?></b>, quitando assim a obrigação descrita.
        <?php if (!empty($obs)): ?>
            <br><br><i>Obs: <?php echo $obs; ?></i>
        <?php endif; ?>
    </div>

    <!-- Assinatura -->
    <div class="assinatura-box">
        <div class="assinatura-linha"></div>
        <div class="assinatura-texto">Assinatura do Responsável</div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <div class="uppercase bold"><?php echo $nome_sistema; ?></div>
        <div><?php echo htmlspecialchars($endereco_sistema); ?></div>
        <div>☎ <?php echo htmlspecialchars($telefone_sistema); ?></div>
        <div class="footer-obs">Recibo gerado em <?php echo date('d/m/Y \à\s H:i'); ?></div>
    </div>

</body>

</html>
