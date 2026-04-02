<?php
require_once("../../../conexao.php");
require_once("../../verificar.php");
require_once("../../funcoes.php");

// ✅ Log para debug (remova depois)
error_log("=== INÍCIO baixar.php ===");
error_log("POST: " . print_r($_POST, true));

// ✅ Busca configurações de multa/juros padrão do sistema
$config = $pdo->query("SELECT multa_padrao, juros_padrao FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$multa_pct = $config['multa_padrao'] ?? 2.00;
$juros_pct = $config['juros_padrao'] ?? 0.33;

// ✅ Recebe dados do formulário
$id = @$_POST['id'];
$valor = @$_POST['valor'];
$forma_pagamento = @$_POST['forma_pagamento'];
$data_pgto = @$_POST['data_pgto'] ?? date('Y-m-d');
$multa_informada = @$_POST['multa'];
$juros_informado = @$_POST['juros'];
$desconto_informado = @$_POST['desconto'];
$taxa_informada = @$_POST['taxa'];

// ✅ Validações básicas COM MENSAGEM ESPECÍFICA
if (empty($id)) {
    error_log("Erro: ID não recebido");
    echo "Erro: ID da conta não informado!";
    exit;
}
if (empty($valor)) {
    error_log("Erro: Valor não recebido");
    echo "Erro: Valor não informado!";
    exit;
}
if (empty($forma_pagamento)) {
    error_log("Erro: Forma de pagamento não recebida");
    echo "Erro: Selecione uma forma de pagamento!";  // ✅ Mensagem clara para o usuário
    exit;
}

// ✅ Converte valores para decimal
$valor_num = floatval(str_replace(['R$', '.', ','], ['', '', '.'], $valor));
$multa_num = !empty($multa_informada) ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $multa_informada)) : 0;
$juros_num = !empty($juros_informado) ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $juros_informado)) : 0;
$desconto_num = !empty($desconto_informado) ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $desconto_informado)) : 0;
$taxa_num = !empty($taxa_informada) ? floatval($taxa_informada) : 0;

error_log("Valores convertidos: valor_num={$valor_num}, multa={$multa_num}, juros={$juros_num}, taxa={$taxa_num}");

try {
    // ✅ Busca dados da conta original
    $stmt = $pdo->prepare("SELECT * FROM pagar WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $conta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conta) {
        error_log("Erro: Conta {$id} não encontrada");
        echo "Erro: Conta não encontrada!";
        exit;
    }

    $data_vencimento = $conta['data_vencimento'];
    error_log("Conta encontrada: vencimento={$data_vencimento}, data_pgto={$data_pgto}");

    // ✅ Calcula multa e juros automáticos SE estiver atrasado
    if ($data_pgto > $data_vencimento) {
        if (empty($multa_informada)) {
            $multa_num = $valor_num * ($multa_pct / 100);
            error_log("Multa automática calculada: {$multa_num}");
        }
        if (empty($juros_informado)) {
            $dias_calculo = calcularDiasAtraso($data_vencimento, $data_pgto, 30);
            $juros_num = $valor_num * ($juros_pct / 100) * ($dias_calculo / 30);
            error_log("Juros automáticos calculados: {$juros_num} ({$dias_calculo} dias)");
        }
    }

    // ✅ Busca taxa da forma de pagamento (se não informado)
    if (empty($taxa_informada)) {
        $stmt_fp = $pdo->prepare("SELECT nome FROM forma_pagamento WHERE id = :id LIMIT 1");
        $stmt_fp->execute([':id' => $forma_pagamento]);
        $fp_nome = strtolower($stmt_fp->fetchColumn() ?? '');
        if (strpos($fp_nome, 'débito') !== false || strpos($fp_nome, 'debito') !== false) {
            $taxa_num = 3;
        } elseif (strpos($fp_nome, 'crédito') !== false || strpos($fp_nome, 'credito') !== false) {
            $taxa_num = 5;
        }
        error_log("Taxa aplicada: {$taxa_num}% (forma: {$fp_nome})");
    }

    // ✅ Calcula subtotal
    $subtotal = $valor_num + $multa_num + $juros_num + ($valor_num * $taxa_num / 100) - $desconto_num;
    error_log("Subtotal calculado: {$subtotal}");

    // ✅ BAIXA NORMAL: atualiza conta como paga
    $stmt_update = $pdo->prepare("UPDATE pagar SET 
        data_pagamento = :data_pgto,
        usuario_pgto = :usuario,
        multa = :multa,
        juros = :juros,
        desconto = :desconto,
        taxa = :taxa,
        subtotal = :subtotal
        WHERE id = :id");

    $stmt_update->execute([
        ':data_pgto' => $data_pgto,
        ':usuario' => $_SESSION['id_user'] ?? 1,
        ':multa' => round($multa_num, 2),
        ':juros' => round($juros_num, 2),
        ':desconto' => round($desconto_num, 2),
        ':taxa' => round($taxa_num, 2),
        ':subtotal' => round($subtotal, 2),
        ':id' => $id
    ]);

    error_log("Baixa realizada com sucesso para conta {$id}");
    echo "Baixado com Sucesso";
    
} catch (Exception $e) {
    error_log("ERRO EXCEPTION: " . $e->getMessage());
    echo "Erro: " . $e->getMessage();
}
?>