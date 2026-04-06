<?php
require_once("../../../conexao.php");
require_once("../../verificar.php");
require_once("../../funcoes.php"); // ✅ Importa a função calcularDiasAtraso()

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
$pagamento_parcial = @$_POST['pagamento_parcial'];
$valor_parcial = @$_POST['valor_parcial'];

// ✅ Validações básicas
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

    // ✅ Calcula multa e juros automáticos SE estiver atrasado (USANDO FUNÇÃO REUTILIZÁVEL)
    if ($data_pgto > $data_vencimento) {
        if (empty($multa_informada)) {
            $multa_num = $valor_num * ($multa_pct / 100);
        }
        if (empty($juros_informado)) {
            // ✅ Usa função centralizada (limita a 30 dias)
            $dias_calculo = calcularDiasAtraso($data_vencimento, $data_pgto, 30);
            $juros_num = $valor_num * ($juros_pct / 100) * ($dias_calculo / 30);
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
    }

    // ✅ Calcula subtotal
    $subtotal = $valor_num + $multa_num + $juros_num + ($valor_num * $taxa_num / 100) - $desconto_num;

    // ✅ VERIFICA SE É PAGAMENTO PARCIAL (RESÍDUO)
    if ($pagamento_parcial == 'on' && !empty($valor_parcial)) {

        // ✅ Converte valor parcial
        $valor_parcial_num = floatval(str_replace(['R$', '.', ','], ['', '', '.'], $valor_parcial));

        // ✅ Calcula subtotal do resíduo (valor parcial + ajustes)
        $subtotal_residuo = $valor_parcial_num + $multa_num + $juros_num + ($valor_parcial_num * $taxa_num / 100) - $desconto_num;

        // ✅ Cria registro de RESÍDUO
        $stmt_residuo = $pdo->prepare("INSERT INTO receber (descricao, paciente, valor, data_vencimento, data_lancamento, data_pagamento,
                                                            forma_pagamento, frequencia, obs, usuario_lanc, usuario_pgto,
                                                            arquivo, referencia, id_referencia, multa, juros, desconto, taxa, subtotal
                                     ) VALUES (:descricao, :paciente, :valor, :data_venc, :data_lanc, :data_pgto,
                                               :forma_pgto, :freq, :obs, :usuario_lanc, :usuario_pgto,
                                               :arquivo, 'Resíduo', :id_referencia, :multa, :juros, :desconto, :taxa, :subtotal)");

        $stmt_residuo->execute([
            ':descricao' => $conta['descricao'] . ' (Resíduo)',
            ':paciente' => $conta['paciente'],
            ':valor' => $valor_parcial_num,
            ':data_venc' => $conta['data_vencimento'],
            ':data_lanc' => date('Y-m-d'),
            ':data_pgto' => $data_pgto,
            ':forma_pgto' => $forma_pagamento,
            ':freq' => $conta['frequencia'],
            ':obs' => 'Pagamento parcial - Resíduo',
            ':usuario_lanc' => $_SESSION['id_usuario'] ?? 1,
            ':usuario_pgto' => $_SESSION['id_usuario'] ?? 1,
            ':arquivo' => !empty($conta['arquivo']) ? 'residuo_' . $conta['arquivo'] : 'aPagar.png',
            ':id_referencia' => $id,
            ':multa' => round($multa_num, 2),
            ':juros' => round($juros_num, 2),
            ':desconto' => round($desconto_num, 2),
            ':taxa' => round($taxa_num, 2),
            ':subtotal' => round($subtotal_residuo, 2)
        ]);

        // ✅ ATUALIZA CONTA ORIGINAL: reduz valor pelo subtotal recebido
        $novo_valor = $conta['valor'] - $subtotal_residuo;
        $novo_subtotal = ($conta['subtotal'] ?? $conta['valor']) - $subtotal_residuo;

        $stmt_update = $pdo->prepare("UPDATE receber SET valor = :novo_valor, subtotal = :novo_subtotal WHERE id = :id");
        $stmt_update->execute([':novo_valor' => round($novo_valor, 2), ':novo_subtotal' => round($novo_subtotal, 2), ':id' => $id]);

        echo "Sucesso: Resíduo de R$ " . number_format($valor_parcial_num, 2, ',', '.') . " registrado! Saldo restante: R$ " . 
                                         number_format($novo_valor, 2, ',', '.');
        exit;
    }

    // ✅ SE NÃO É PARCIAL: segue com baixa normal (conta quitada)
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
        ':data_pgto' => $data_pgto,
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
?>