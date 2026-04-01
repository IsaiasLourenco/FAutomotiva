<?php
require_once("../../../conexao.php");
require_once("../../verificar.php");

$ids = @$_POST['ids'] ?? [];
$forma_pagamento = @$_POST['forma_pagamento'] ?? '';
$data_pgto = @$_POST['data_pgto'] ?? date('Y-m-d');
$aplicar_multas = @$_POST['aplicar_multas'] ?? 'nao';

if (empty($ids) || !is_array($ids)) {
    echo "Erro: Nenhum ID recebido!";
    exit;
}
if (!$forma_pagamento) {
    echo "Erro: Forma de pagamento não informada!";
    exit;
}

try {
    $contador = 0;
    $total_geral = 0;

    foreach ($ids as $id) {
        if (!$id) continue;

        $stmt = $pdo->prepare("SELECT * FROM receber WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $conta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$conta) continue;

        // Só baixa se não estiver paga
        if (!empty($conta['data_pagamento']) && $conta['data_pagamento'] != '0000-00-00') continue;

        // ✅ CALCULA VALOR RESTANTE (considera resíduos já pagos)
        $valor_original = $conta['valor'];
        $total_residuos = 0;

        if (empty($conta['referencia']) || $conta['referencia'] != 'Resíduo') {
            $stmt_res = $pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM receber WHERE id_referencia = :id AND referencia = 'Resíduo'");
            $stmt_res->execute([':id' => $id]);
            $total_residuos = $stmt_res->fetchColumn();
        }

        $valor = $valor_original - $total_residuos;
        if ($valor <= 0) continue; // Já foi totalmente pago via resíduos

        $data_vencimento = $conta['data_vencimento'];
        $multa = 0;
        $juros = 0;
        $desconto = 0;
        $taxa = 0;

        // Busca taxa da forma de pagamento
        $stmt_fp = $pdo->prepare("SELECT nome FROM forma_pagamento WHERE id = :id LIMIT 1");
        $stmt_fp->execute([':id' => $forma_pagamento]);
        $fp_nome = strtolower($stmt_fp->fetchColumn() ?? '');
        if (strpos($fp_nome, 'débito') !== false || strpos($fp_nome, 'debito') !== false) $taxa = 3;
        elseif (strpos($fp_nome, 'crédito') !== false || strpos($fp_nome, 'credito') !== false) $taxa = 5;

        // Aplica multa/juros se vencida
        if ($aplicar_multas == 'sim' && $data_pgto > $data_vencimento) {
            $dias = (strtotime($data_pgto) - strtotime($data_vencimento)) / 86400;
            $multa = $valor * 0.02;
            $juros = $valor * 0.01 * ($dias / 30);
        }

        // Calcula subtotal sobre o valor RESTANTE
        $subtotal = $valor + $multa + $juros + ($valor * $taxa / 100) - $desconto;

        // Atualiza conta
        $stmt_update = $pdo->prepare("UPDATE receber SET 
            data_pagamento = :data_pgto, usuario_pgto = :usuario,
            multa = :multa, juros = :juros, desconto = :desconto,
            taxa = :taxa, subtotal = :subtotal WHERE id = :id");
        $stmt_update->execute([
            ':data_pgto' => $data_pgto,
            ':usuario' => $_SESSION['id_usuario'] ?? 1,
            ':multa' => round($multa, 2),
            ':juros' => round($juros, 2),
            ':desconto' => round($desconto, 2),
            ':taxa' => round($taxa, 2),
            ':subtotal' => round($subtotal, 2),
            ':id' => $id
        ]);

        $contador++;
        $total_geral += $subtotal;
    }

    echo "Sucesso: {$contador} conta(s) baixada(s)! Total: R$ " . number_format($total_geral, 2, ',', '.');
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
