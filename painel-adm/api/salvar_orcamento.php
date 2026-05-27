<?php
// FAutomotiva/panel-adm/api/salvar_orcamento.php
session_start();
require_once '../../conexao.php';

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Salva cliente se não existir
    $cliente_id = $_POST['cliente_id'] ?? null;
    if (!$cliente_id) {
        $stmt = $pdo->prepare("INSERT INTO clientes (nome, cpf, telefone, cep, rua, numero, bairro, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['cliente_nome'],
            $_POST['cliente_cpf'] ?? null,
            $_POST['cliente_cel'] ?? null,
            $_POST['cep'] ?? null,
            $_POST['rua'] ?? null,
            $_POST['numero'] ?? null,
            '', '', ''
        ]);
        $cliente_id = $pdo->lastInsertId();
    }

    // Salva veículo
    $stmt = $pdo->prepare("INSERT INTO veiculos (cliente_id, placa, modelo, motor, ano, cor, km_atual) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $cliente_id,
        $_POST['veiculo_placa'],
        $_POST['veiculo_modelo'],
        $_POST['veiculo_motor'] ?? null,
        $_POST['veiculo_ano'] ?? null,
        $_POST['veiculo_cor'] ?? null,
        str_replace('.', '', $_POST['veiculo_km'] ?? '0')
    ]);
    $veiculo_id = $pdo->lastInsertId();

    // Salva orçamento
    $valor_total = str_replace(['.', ','], ['', '.'], $_POST['valor_total'] ?? '0');

    $stmt = $pdo->prepare("INSERT INTO orcamentos (cliente_id, veiculo_id, usuario_id, valor_total, status, data_criacao) VALUES (?, ?, ?, ?, 'rascunho', NOW())");
    $stmt->execute([$cliente_id, $veiculo_id, $_SESSION['id_user'], $valor_total]);
    $orcamento_id = $pdo->lastInsertId();

    // Salva itens do orçamento (peças/serviços)
    $mec = $_POST['mec'] ?? [];
    $fornecedores_data = $_POST['fornecedor'] ?? [];

    foreach ($mec as $index => $mecan) {
        if (!empty($mecan)) {
            // Busca peça por nome
            $stmt = $pdo->prepare("SELECT id FROM pecas WHERE nome_padrao = ?");
            $stmt->execute([$mecan]);
            $peca = $stmt->fetch();

            if (!$peca) {
                // Cria peça se não existir
                $stmt = $pdo->prepare("INSERT INTO pecas (nome_padrao, ativo) VALUES (?, 1)");
                $stmt->execute([$mecan]);
                $peca_id = $pdo->lastInsertId();
            } else {
                $peca_id = $peca['id'];
            }

            // Salva item no orçamento
            $stmt = $pdo->prepare("INSERT INTO orcamento_itens (orcamento_id, peca_id, quantidade, observacao) VALUES (?, ?, 1, ?)");
            $stmt->execute([$orcamento_id, $peca_id, $mecan]);

            // Salva cotações por fornecedor
            foreach ($fornecedores_data as $fornecedor_id => $valores) {
                if (isset($valores[$index]) && !empty($valores[$index])) {
                    $valor = str_replace(['.', ','], ['', '.'], $valores[$index]);

                    $stmt = $pdo->prepare("INSERT INTO cotacao_itens (orcamento_id, fornecedor_id, peca_id, preco_custo) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$orcamento_id, $fornecedor_id, $peca_id, $valor]);
                }
            }
        }
    }

    $pdo->commit();
    echo json_encode(['sucesso' => true, 'id' => $orcamento_id]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
?>
