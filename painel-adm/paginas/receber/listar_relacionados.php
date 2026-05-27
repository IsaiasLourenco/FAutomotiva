<?php
require_once("../../../conexao.php");

$id_original = $_POST['id'] ?? 0;
$resumo = $_POST['resumo'] ?? 'nao';

// ✅ 1. Identificar a conta principal
// Se a conta tem id_referencia, ela é parcela/resíduo → buscar a principal
// Se não tem, ela É a principal
$stmt_principal = $pdo->prepare("
    SELECT id, descricao, valor, subtotal, paciente, referencia, id_referencia
    FROM receber
    WHERE id = :id LIMIT 1
");
$stmt_principal->execute([':id' => $id_original]);
$conta_original = $stmt_principal->fetch(PDO::FETCH_ASSOC);

if (!$conta_original) {
    echo '<tr><td colspan="5" class="text-center text-danger">Conta não encontrada.</td></tr>';
    exit;
}

// ✅ Determinar qual é o ID da conta principal
$id_principal = !empty($conta_original['id_referencia']) && $conta_original['id_referencia'] != $id_original
    ? $conta_original['id_referencia']
    : $id_original;

// ✅ 2. Buscar dados da conta principal (para exibir no topo)
$stmt_info = $pdo->prepare("
    SELECT r.*, p.nome as paciente_nome
    FROM receber r
    LEFT JOIN clientes p ON r.paciente = p.id
    WHERE r.id = :id LIMIT 1
");
$stmt_info->execute([':id' => $id_principal]);
$info_principal = $stmt_info->fetch(PDO::FETCH_ASSOC);

// ✅ 3. Buscar TODAS as contas relacionadas (parcelas + resíduos)
$stmt_rel = $pdo->prepare("
    SELECT r.*, p.nome as paciente_nome, fp.nome as forma_nome
    FROM receber r
    LEFT JOIN clientes p ON r.paciente = p.id
    LEFT JOIN forma_pagamento fp ON r.forma_pagamento = fp.id
    WHERE r.id_referencia = :id_principal OR r.id = :id_principal
    ORDER BY r.data_vencimento ASC, r.id ASC
");
$stmt_rel->execute([':id_principal' => $id_principal]);
$relacionados = $stmt_rel->fetchAll(PDO::FETCH_ASSOC);

// ✅ 4. Calcular totais
$valor_original = $info_principal['valor'] ?? 0;
$total_pago = 0;
$total_pendente = 0;

foreach ($relacionados as $r) {
    if (!empty($r['data_pagamento']) && $r['data_pagamento'] != '0000-00-00') {
        $total_pago += $r['subtotal'] ?? $r['valor'] ?? 0;
    } else {
        $total_pendente += $r['subtotal'] ?? $r['valor'] ?? 0;
    }
}
$saldo_restante = $valor_original - $total_pago;

// ✅ 5. Se for pedido de resumo (AJAX), retornar JSON
if ($resumo === 'sim') {
    header('Content-Type: application/json');
    echo json_encode([
        'sucesso' => true,
        'total_pago' => 'R$ ' . number_format($total_pago, 2, ',', '.'),
        'saldo_restante' => 'R$ ' . number_format(max($saldo_restante, 0), 2, ',', '.'),
        'valor_original' => 'R$ ' . number_format($valor_original, 2, ',', '.')
    ]);
    exit;
}

// ✅ 6. Gerar HTML da tabela
?>

<!-- ✅ Cabeçalho: Conta Principal -->
<div class="card mb-3 border-info">
    <div class="card-header bg-info text-white py-2">
        <small><i class="fa fa-link"></i> Conta Principal</small>
    </div>
    <div class="card-body py-2">
        <div class="row small">
            <div class="col-md-4"><strong>Descrição:</strong> <?php echo htmlspecialchars($info_principal['descricao']); ?></div>
            <div class="col-md-3"><strong>Paciente:</strong> <?php echo htmlspecialchars($info_principal['paciente_nome']); ?></div>
            <div class="col-md-2"><strong>Valor Original:</strong> R$ <?php echo number_format($valor_original, 2, ',', '.'); ?></div>
            <div class="col-md-3">
                <strong>Vencimento:</strong>
                    <?php echo !empty($info_principal['data_vencimento']) && $info_principal['data_vencimento'] != '0000-00-00' ? date('d/m/Y', strtotime($info_principal['data_vencimento'])) : '-'; ?></div>
        </div>
    </div>
</div>

<!-- ✅ Tabela de Parcelas/Resíduos -->
<table class="table table-sm table-hover mb-0">
    <thead class="bg-light">
        <tr>
            <th width="5%" class="text-center">Tipo</th>
            <th width="30%">Descrição</th>
            <th width="15%" class="text-center">Vencimento</th>
            <th width="15%" class="text-center">Pago em</th>
            <th width="15%" class="text-center">Forma</th>
            <th width="20%" class="text-end">Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($relacionados)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-3">Nenhum registro relacionado encontrado.</td>
            </tr>
            <?php else: foreach ($relacionados as $r):
                $tipo = $r['referencia'] ?? 'Conta';
                $status_pago = !empty($r['data_pagamento']) && $r['data_pagamento'] != '0000-00-00';
                $classe_status = $status_pago ? 'text-success' : 'text-warning';
                $texto_pago = $status_pago ? date('d/m/Y', strtotime($r['data_pagamento'])) : 'Pendente';
                $valor_exibir = $r['subtotal'] ?? $r['valor'] ?? 0;
            ?>
                <tr>
                    <td class="text-center small">
                        <span class="badge badge-<?php echo $tipo === 'Parcela' ? 'primary' : ($tipo === 'Resíduo' ? 'warning' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($tipo); ?>
                        </span>
                    </td>
                    <td class="small"><?php echo htmlspecialchars($r['descricao']); ?></td>
                    <td class="text-center small">
                        <?php echo !empty($r['data_vencimento']) && $r['data_vencimento'] != '0000-00-00' ? date('d/m/Y', strtotime($r['data_vencimento'])) : '-'; ?></td>
                    <td class="text-center small <?php echo $classe_status; ?>"><?php echo $texto_pago; ?></td>
                    <td class="text-center small"><?php echo htmlspecialchars($r['forma_nome'] ?? '-'); ?></td>
                    <td class="text-end small font-weight-bold">R$ <?php echo number_format($valor_exibir, 2, ',', '.'); ?></td>
                </tr>
        <?php endforeach;
        endif; ?>
    </tbody>
</table>

<!-- ✅ Rodapé com Resumo (sempre visível agora) -->
<div class="row mt-3 pt-3 border-top bg-light rounded p-2">
    <div class="col-md-4 text-center">
        <small class="text-muted d-block">Total Pago</small>
        <h5 class="text-success font-weight-bold mb-0">R$ <?php echo number_format($total_pago, 2, ',', '.'); ?></h5>
    </div>
    <div class="col-md-4 text-center">
        <small class="text-muted d-block">Saldo Restante</small>
        <h5 class="<?php echo $saldo_restante <= 0 ? 'text-success' : 'text-danger'; ?> font-weight-bold mb-0">
            R$ <?php echo number_format(max($saldo_restante, 0), 2, ',', '.'); ?>
        </h5>
    </div>
    <div class="col-md-4 text-center">
        <small class="text-muted d-block">Valor Original</small>
        <h5 class="text-dark font-weight-bold mb-0">R$ <?php echo number_format($valor_original, 2, ',', '.'); ?></h5>
    </div>
</div>

<!-- ✅ Indicador de Status Final -->
<div class="text-center mt-2">
    <?php if ($saldo_restante <= 0): ?>
        <span class="badge badge-success py-2 px-3"><i class="fa fa-check-circle"></i> Conta Quitada</span>
    <?php else: ?>
        <span class="badge badge-warning py-2 px-3"><i class="fa fa-clock"></i> Aguardando R$ <?php echo number_format($saldo_restante, 2, ',', '.'); ?></span>
    <?php endif; ?>
</div>
