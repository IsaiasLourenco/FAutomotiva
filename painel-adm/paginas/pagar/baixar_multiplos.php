<?php
require_once("../../../conexao.php");
require_once("../../verificar.php");
require_once("../../funcoes.php"); // ✅ Importa a função calcularDiasAtraso()

// ✅ Busca configurações de multa/juros padrão do sistema
$config = $pdo->query("SELECT multa_padrao, juros_padrao FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$multa_pct = $config['multa_padrao'] ?? 2.00;
$juros_pct = $config['juros_padrao'] ?? 0.33;

$ids = @$_POST['ids'] ?? [];
$forma_pagamento = @$_POST['forma_pagamento'] ?? '';
$data_pgto = @$_POST['data_pgto'] ?? date('Y-m-d');
$aplicar_multas = @$_POST['aplicar_multas'] ?? 'nao';

if (empty($ids) || !is_array($ids)) { echo "Erro: Nenhum ID recebido!"; exit; }
if (!$forma_pagamento) { echo "Erro: Forma de pagamento não informada!"; exit; }

try {
    $contador = 0;
    $total_geral = 0;

    foreach ($ids as $id) {
        if (!$id) continue;

        // ✅ Busca conta na tabela PAGAR
        $stmt = $pdo->prepare("SELECT * FROM pagar WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $conta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$conta) continue;

        // ✅ Só baixa se não estiver paga
        if (!empty($conta['data_pagamento']) && $conta['data_pagamento'] != '0000-00-00') continue;

        $valor = $conta['valor'];
        $data_vencimento = $conta['data_vencimento'];
        $multa = 0; $juros = 0; $desconto = 0; $taxa = 0;

        // ✅ Busca taxa da forma de pagamento
        $stmt_fp = $pdo->prepare("SELECT nome FROM forma_pagamento WHERE id = :id LIMIT 1");
        $stmt_fp->execute([':id' => $forma_pagamento]);
        $fp_nome = strtolower($stmt_fp->fetchColumn() ?? '');
        if (strpos($fp_nome, 'débito') !== false || strpos($fp_nome, 'debito') !== false) {
            $taxa = 3;
        } elseif (strpos($fp_nome, 'crédito') !== false || strpos($fp_nome, 'credito') !== false) {
            $taxa = 5;
        }

        // ✅ Aplica multa/juros se vencida E se opção estiver marcada (USANDO FUNÇÃO REUTILIZÁVEL)
        if ($aplicar_multas == 'sim' && $data_pgto > $data_vencimento) {
            $multa = $valor * ($multa_pct / 100);
            $dias_calculo = calcularDiasAtraso($data_vencimento, $data_pgto, 30);
            $juros = $valor * ($juros_pct / 100) * ($dias_calculo / 30);
        }

        // ✅ Calcula subtotal
        $subtotal = $valor + $multa + $juros + ($valor * $taxa / 100) - $desconto;

        // ✅ Atualiza conta na tabela PAGAR
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
            ':usuario' => $_SESSION['id_user'] ?? 1,  // ✅ Corrigido: id_usuario → id_user
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
?>