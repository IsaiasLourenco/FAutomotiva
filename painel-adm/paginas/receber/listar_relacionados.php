<?php
require_once("../../../conexao.php");
require_once("../../verificar.php");

$id_original = @$_POST['id'] ?? 0;

if (!$id_original) {
    echo "<tr><td colspan='5' class='text-center text-muted'>Nenhum registro relacionado encontrado</td></tr>";
    exit;
}

// ✅ Busca parcelas e resíduos vinculados a esta conta
$query = "SELECT * FROM receber 
          WHERE id_referencia = :id_orig 
          ORDER BY data_lancamento DESC, id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([':id_orig' => $id_original]);
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($res)) {
    echo "<tr><td colspan='5' class='text-center text-muted'>Nenhum registro relacionado encontrado</td></tr>";
    exit;
}

foreach ($res as $r) {
    $tipo = $r['referencia'] ?? 'Outro';
    $classe = ($tipo == 'Parcela') ? 'table-info' : (($tipo == 'Resíduo') ? 'table-success' : '');
    $icone = ($tipo == 'Parcela') ? '🧩' : (($tipo == 'Resíduo') ? '💰' : '📄');

    $valorF = 'R$ ' . number_format($r['valor'], 2, ',', '.');
    $subtotalF = 'R$ ' . number_format($r['subtotal'] ?? $r['valor'], 2, ',', '.');
    $dataF = (!empty($r['data_pagamento']) && $r['data_pagamento'] != '0000-00-00')
        ? date('d/m/Y', strtotime($r['data_pagamento']))
        : '<span class="text-muted">Pendente</span>';

    $descricao = htmlspecialchars($r['descricao']);

    // ✅ BUSCA NOME DA FORMA DE PAGAMENTO (em vez de mostrar o ID)
    $forma_pgto = '-';
    if (!empty($r['forma_pagamento'])) {
        $stmt_fp = $pdo->prepare("SELECT nome FROM forma_pagamento WHERE id = :id LIMIT 1");
        $stmt_fp->execute([':id' => $r['forma_pagamento']]);
        $fp_nome = $stmt_fp->fetchColumn();
        $forma_pgto = $fp_nome ? htmlspecialchars($fp_nome) : '-';
    }

    echo <<<HTML
    <tr class="{$classe}">
        <td class="text-center">{$icone}</td>
        <td><small class="text-muted">{$tipo}</small><br><strong>{$descricao}</strong></td>
        <td class="text-center">{$dataF}</td>
        <td class="text-center">{$forma_pgto}</td>
        <td class="text-end">
            <small class="d-block">Valor: {$valorF}</small>
            <small class="text-success">Recebido: {$subtotalF}</small>
        </td>
    </tr>
HTML;
}

// ✅ Se for pedido resumo, retorna JSON
if (@$_POST['resumo'] == 'sim') {
    $stmt_orig = $pdo->prepare("SELECT valor, subtotal FROM receber WHERE id = :id LIMIT 1");
    $stmt_orig->execute([':id' => $id_original]);
    $orig = $stmt_orig->fetch(PDO::FETCH_ASSOC);

    $valor_original = 'R$ ' . number_format($orig['valor'] ?? 0, 2, ',', '.');
    $total_pago = 'R$ ' . number_format(array_sum(array_column($res, 'subtotal')), 2, ',', '.');
    $saldo = 'R$ ' . number_format(($orig['valor'] ?? 0) - array_sum(array_column($res, 'subtotal')), 2, ',', '.');

    header('Content-Type: application/json');
    echo json_encode([
        'sucesso' => true,
        'valor_original' => $valor_original,
        'total_pago' => $total_pago,
        'saldo_restante' => $saldo
    ]);
    exit;
}
