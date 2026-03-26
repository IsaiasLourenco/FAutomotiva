<?php
require_once("../../../conexao.php");
require_once("../../verificar.php");

// ✅ Recebe dados do formulário
$id = @$_POST['id'];
$valor = @$_POST['valor'];
$forma_pagamento = @$_POST['forma_pagamento'];
$data_pgto = @$_POST['data_pgto'];
$multa_informada = @$_POST['multa'];
$juros_informado = @$_POST['juros'];
$desconto_informado = @$_POST['desconto'];
$taxa_informada = @$_POST['taxa'];

// ✅ Validações
if (!$id || !$valor || !$forma_pagamento) {
    echo "Erro: Dados inválidos!";
    exit;
}

// ✅ Converte valores para decimal
$valor_num = floatval(str_replace(['R$', '.', ','], ['', '', '.'], $valor));
$multa_num = !empty($multa_informada) ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $multa_informada)) : 0;
$juros_num = !empty($juros_informado) ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $juros_informado)) : 0;
$desconto_num = !empty($desconto_informado) ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $desconto_informado)) : 0;
$taxa_num = !empty($taxa_informada) ? floatval($taxa_informada) : 0;

try {
    // ✅ Busca dados da conta original
    $stmt = $pdo->prepare("SELECT * FROM receber WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $conta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conta) {
        echo "Erro: Conta não encontrada!";
        exit;
    }

    $data_vencimento = $conta['data_vencimento'];

    // ✅ Calcula multa e juros automáticos SE estiver atrasado
    if ($data_pgto > $data_vencimento) {
        $dias_atraso = (strtotime($data_pgto) - strtotime($data_vencimento)) / 86400;

        // Multa padrão: 2% do valor (se não informado)
        if (empty($multa_informada)) {
            $multa_num = $valor_num * 0.02;
        }

        // Juros padrão: 1% ao mês proporcional aos dias (se não informado)
        if (empty($juros_informado)) {
            $juros_num = $valor_num * 0.01 * ($dias_atraso / 30);
        }
    }

    // ✅ Busca taxa da forma de pagamento (se for cartão e não informado)
    if (empty($taxa_informada)) {
        $stmt_fp = $pdo->prepare("SELECT nome FROM forma_pagamento WHERE id = :id LIMIT 1");
        $stmt_fp->execute([':id' => $forma_pagamento]);
        $fp_nome = strtolower($stmt_fp->fetchColumn() ?? '');

        if (strpos($fp_nome, 'débito') !== false || strpos($fp_nome, 'debito') !== false) {
            $taxa_num = 3; // 3% para débito
        } elseif (strpos($fp_nome, 'crédito') !== false || strpos($fp_nome, 'credito') !== false) {
            $taxa_num = 5; // 5% para crédito
        }
    }

    // ✅ Calcula subtotal
    $subtotal = $valor_num + $multa_num + $juros_num + ($valor_num * $taxa_num / 100) - $desconto_num;

    // ✅ Atualiza a conta com a baixa
    $stmt_update = $pdo->prepare("UPDATE receber SET 
        data_pagamento = :data_pgto,
        usuario_pgto = :usuario,
        multa = :multa,
        juros = :juros,
        desconto = :desconto,
        taxa = :taxa,
        subtotal = :subtotal
        WHERE id = :id");

    $stmt_update->execute([
        ':data_pgto' => $data_pgto ?? date('Y-m-d'),
        ':usuario' => $_SESSION['id_usuario'] ?? 1,
        ':multa' => round($multa_num, 2),
        ':juros' => round($juros_num, 2),
        ':desconto' => round($desconto_num, 2),
        ':taxa' => round($taxa_num, 2),
        ':subtotal' => round($subtotal, 2),
        ':id' => $id
    ]);

    echo "Baixado com Sucesso";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
