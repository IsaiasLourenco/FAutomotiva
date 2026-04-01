<?php
require_once("../../../conexao.php");
require_once("../../verificar.php");
require_once("../../../funcoes.php"); // ✅ Importa a função calcularDiasAtraso()

// ✅ Busca configurações de multa/juros padrão do sistema
$config = $pdo->query("SELECT multa_padrao, juros_padrao FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$multa_pct = $config['multa_padrao'] ?? 2.00;
$juros_pct = $config['juros_padrao'] ?? 0.33;

// ✅ Recebe dados do formulário
$id_original = @$_POST['id-parcelar'];
$valor_total = @$_POST['valor-parcelar'];
$qtd_parcelas = @$_POST['qtd-parcelar'];
$data_primeira = @$_POST['data-primeira'];
$forma_pagamento = @$_POST['forma_pagamento'];
$frequencia_id = @$_POST['frequencia'];
$descricao_original = @$_POST['descricao-original'];

// ✅ Novos campos de ajustes financeiros
$multa = @$_POST['multa'];
$juros = @$_POST['juros'];
$desconto = @$_POST['desconto'];
$taxa = @$_POST['taxa'];

// ✅ Validações
if (!$id_original) { echo "Erro: ID da conta não informado!"; exit; }
if (!$valor_total) { echo "Erro: Valor não informado!"; exit; }
if (!$data_primeira) { echo "Erro: Data da primeira parcela não informada!"; exit; }
if (!$frequencia_id) { echo "Erro: Frequência não selecionada!"; exit; }
if (!$forma_pagamento) { echo "Erro: Forma de pagamento não selecionada!"; exit; }

// ✅ Converte valores para decimal
$valor_total_num = floatval(str_replace(['R$', '.', ','], ['', '', '.'], $valor_total));

// ✅ Se multa/juros estiverem vazios ("Auto"), calcula automaticamente se a conta original estiver vencida
$hoje = date('Y-m-d');
$stmt_orig_check = $pdo->prepare("SELECT data_vencimento FROM receber WHERE id = :id LIMIT 1");
$stmt_orig_check->execute([':id' => $id_original]);
$conta_check = $stmt_orig_check->fetch(PDO::FETCH_ASSOC);
$data_vencimento_original = $conta_check['data_vencimento'] ?? null;

// Multa automática (se vazia e conta vencida)
if (empty($multa) && $data_vencimento_original && $data_vencimento_original < $hoje) {
    $multa_num = $valor_total_num * ($multa_pct / 100);
} else {
    $multa_num = !empty($multa) ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $multa)) : 0;
}

// Juros automáticos (se vazios e conta vencida) - USANDO FUNÇÃO REUTILIZÁVEL
if (empty($juros) && $data_vencimento_original && $data_vencimento_original < $hoje) {
    // ✅ Usa função centralizada (limita a 30 dias)
    $dias_calculo = calcularDiasAtraso($data_vencimento_original, $hoje, 30);
    $juros_num = $valor_total_num * ($juros_pct / 100) * ($dias_calculo / 30);
} else {
    $juros_num = !empty($juros) ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $juros)) : 0;
}

$desconto_num = !empty($desconto) ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $desconto)) : 0;
$taxa_num = !empty($taxa) ? floatval($taxa) : 0;

try {
    $pdo->beginTransaction();

    // ✅ 1. Busca o paciente e arquivo da conta original
    $stmt_orig = $pdo->prepare("SELECT paciente, arquivo FROM receber WHERE id = :id LIMIT 1");
    $stmt_orig->execute([':id' => $id_original]);
    $conta_orig = $stmt_orig->fetch(PDO::FETCH_ASSOC);
    $paciente_id = $conta_orig['paciente'];

    if (!$paciente_id) { throw new Exception("Conta original não encontrada!"); }

    $arquivo_parcela = !empty($conta_orig['arquivo']) && $conta_orig['arquivo'] !== 'sem-foto.png'
        ? $conta_orig['arquivo'] : 'aPagar.png';

    // ✅ 2. Marca conta original como "Parcelada"
    $stmt = $pdo->prepare("UPDATE receber SET descricao = CONCAT(descricao, ' (Parcelada)') WHERE id = :id");
    $stmt->execute([':id' => $id_original]);

    // ✅ 3. Calcula valor total ajustado
    $valor_ajustado = $valor_total_num + $multa_num + $juros_num - $desconto_num;
    if ($taxa_num > 0) { $valor_ajustado += $valor_ajustado * ($taxa_num / 100); }

    // ✅ 4. Calcula valor base por parcela
    $valor_base = floor(($valor_ajustado / $qtd_parcelas) * 100) / 100;
    $resto = round(($valor_ajustado - ($valor_base * $qtd_parcelas)) * 100);

    // ✅ 5. Busca dados da frequência para cálculo de datas
    $stmt_freq = $pdo->prepare("SELECT frequencia, dias FROM frequencias WHERE id = :id LIMIT 1");
    $stmt_freq->execute([':id' => $frequencia_id]);
    $freq_info = $stmt_freq->fetch(PDO::FETCH_ASSOC);
    $nome_frequencia = strtolower($freq_info['frequencia'] ?? '');
    $dias_frequencia = $freq_info['dias'] ?? 30;

    // ✅ 6. Cria as parcelas
    $data_vencimento = $data_primeira;

    for ($i = 1; $i <= $qtd_parcelas; $i++) {
        $valor_parcela = $valor_base;
        if ($i == $qtd_parcelas && $resto != 0) { $valor_parcela += $resto / 100; }

        $descricao_parcela = "{$descricao_original} - Parcela {$i}/{$qtd_parcelas}";

        // ✅ Rateio dos ajustes por parcela
        $multa_parcela = $multa_num > 0 ? $multa_num / $qtd_parcelas : null;
        $juros_parcela = $juros_num > 0 ? $juros_num / $qtd_parcelas : null;
        $desconto_parcela = $desconto_num > 0 ? $desconto_num / $qtd_parcelas : null;
        $taxa_parcela = $taxa_num > 0 ? $taxa_num : null;
        $subtotal_parcela = $valor_parcela;

        // ✅ 7. INSERT da parcela
        $stmt = $pdo->prepare("INSERT INTO receber (descricao, paciente, valor, data_vencimento, data_lancamento, 
                                                    forma_pagamento, frequencia, obs, usuario_lanc, usuario_pgto,
                                                    arquivo, referencia, id_referencia, multa, juros, desconto, taxa, subtotal) 
                                      VALUES (:descricao, :paciente, :valor, :data_venc, :data_lanc, :forma_pgto, :freq_id, :obs, :usuario_lanc, NULL,
                                              :arquivo, 'Parcela', :id_orig, :multa, :juros, :desconto, :taxa, :subtotal)");

        $stmt->execute([
            ':descricao' => $descricao_parcela,
            ':paciente' => $paciente_id,
            ':valor' => round($valor_parcela, 2),
            ':data_venc' => $data_vencimento,
            ':data_lanc' => date('Y-m-d'),
            ':forma_pgto' => $forma_pagamento,
            ':freq_id' => $frequencia_id,
            ':obs' => "Parcela {$i} de {$qtd_parcelas}",
            ':usuario_lanc' => $_SESSION['id_user'] ?? 1,
            ':id_orig' => $id_original,
            ':multa' => $multa_parcela > 0 ? round($multa_parcela, 2) : null,
            ':juros' => $juros_parcela > 0 ? round($juros_parcela, 2) : null,
            ':desconto' => $desconto_parcela > 0 ? round($desconto_parcela, 2) : null,
            ':taxa' => $taxa_parcela > 0 ? round($taxa_parcela, 2) : 0.00,
            ':subtotal' => round($subtotal_parcela, 2),
            ':arquivo' => $arquivo_parcela
        ]);

        // ✅ 8. Avança data corretamente
        $data_vencimento = avancarData($data_vencimento, $nome_frequencia, $dias_frequencia);
    }

    $pdo->commit();
    echo "Sucesso: {$qtd_parcelas} parcelas criadas!";
} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo "Erro: " . $e->getMessage();
}

// ✅ Função para avançar data corretamente
function avancarData($data, $nome_frequencia, $dias) {
    $timestamp = strtotime($data);
    if (strpos($nome_frequencia, 'mensal') !== false) { $timestamp = strtotime('+1 month', $timestamp); }
    elseif (strpos($nome_frequencia, 'bimestral') !== false) { $timestamp = strtotime('+2 months', $timestamp); }
    elseif (strpos($nome_frequencia, 'trimestral') !== false) { $timestamp = strtotime('+3 months', $timestamp); }
    elseif (strpos($nome_frequencia, 'quinzenal') !== false) { $timestamp = strtotime('+15 days', $timestamp); }
    elseif (strpos($nome_frequencia, 'semanal') !== false) { $timestamp = strtotime('+7 days', $timestamp); }
    elseif (strpos($nome_frequencia, 'diário') !== false || strpos($nome_frequencia, 'diario') !== false) { $timestamp = strtotime('+1 day', $timestamp); }
    else { $timestamp = strtotime("+{$dias} days", $timestamp); }
    return date('Y-m-d', $timestamp);
}
?>