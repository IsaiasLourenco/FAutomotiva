<?php
// FAutomotiva/painel-adm/api/buscar_veiculo_por_placa.php
require_once("../../conexao.php");
header('Content-Type: application/json');

$placa = strtoupper(trim($_GET['placa'] ?? ''));
$placa = preg_replace('/[^A-Z0-9]/', '', $placa); // Remove hífens, espaços

if (strlen($placa) < 6) {
    echo json_encode(['encontrado' => false, 'mensagem' => 'Placa incompleta']);
    exit;
}

try {
    // Busca veículo pela placa (com ou sem hífen)
    $stmt = $pdo->prepare("
        SELECT v.*, c.nome as cliente_nome, c.telefone as cliente_telefone
        FROM veiculos v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE REPLACE(v.placa, '-', '') = :placa
        LIMIT 1
    ");
    $stmt->execute(['placa' => $placa]);
    $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($veiculo) {
        echo json_encode([
            'encontrado' => true,
            'id' => $veiculo['id'],
            'placa' => $veiculo['placa'],
            'marca' => $veiculo['marca'] ?? '',
            'modelo' => $veiculo['modelo'] ?? '',
            'ano' => $veiculo['ano'] ?? '',
            'motor' => $veiculo['motor'] ?? '',
            'km_atual' => $veiculo['km_atual'] ?? '',
            'cliente_id' => $veiculo['cliente_id'] ?? '',
            'cliente_nome' => $veiculo['cliente_nome'] ?? '',
            'cliente_telefone' => $veiculo['cliente_telefone'] ?? '',
            'observacoes' => $veiculo['observacoes'] ?? ''
        ]);
    } else {
        echo json_encode(['encontrado' => false, 'mensagem' => 'Veículo não cadastrado']);
    }
} catch (Exception $e) {
    echo json_encode(['encontrado' => false, 'mensagem' => 'Erro na busca']);
}
?>
