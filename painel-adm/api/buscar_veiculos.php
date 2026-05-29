<?php
// FAutomotiva/painel-adm/api/buscar_veiculos.php
require_once "../../conexao.php";
header('Content-Type: application/json');

// Configura PDO para lançar exceções
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$q = strtoupper(trim($_GET['q'] ?? ''));
$cliente_id = $_GET['cliente_id'] ?? null;

// Permite busca com 1+ caractere (para testar)
if (strlen($q) < 1 && !$cliente_id) {
    echo json_encode(['debug' => 'q muito curto e sem cliente_id']);
    exit;
}

// Query EXATA que será executada
$sql = "SELECT v.id, v.placa, v.modelo, v.marca, v.motor, v.ano, v.km_atual, v.cor, c.nome as cliente_nome
        FROM veiculos v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE (v.placa LIKE :q1 OR v.modelo LIKE :q2 OR v.marca LIKE :q3)";

$params = [
    'q1' => "%$q%",
    'q2' => "%$q%",
    'q3' => "%$q%"
];

// Se tiver cliente_id, adiciona filtro
if ($cliente_id) {
    $sql .= " AND v.cliente_id = :cid";
    $params['cid'] = $cliente_id;
}

$sql .= " ORDER BY v.placa LIMIT 15";

try {
    // Prepara e executa
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 👇 DEBUG VISÍVEL - REMOVA DEPOIS QUE FUNCIONAR 👇
    if (empty($resultados)) {
        // Conta total de veículos para debug
        $total_veiculos = $pdo->query("SELECT COUNT(*) FROM veiculos")->fetchColumn();

        // Testa query sem filtro de cliente para ver se acha algo
        $sql_teste = "SELECT placa, modelo, marca FROM veiculos WHERE placa LIKE :q OR modelo LIKE :q OR marca LIKE :q LIMIT 5";
        $stmt_teste = $pdo->prepare($sql_teste);
        $stmt_teste->execute(['q' => "%$q%"]);
        $teste_result = $stmt_teste->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'debug' => true,
            'query' => $sql,
            'params' => $params,
            'resultados_encontrados' => count($resultados),
            'total_veiculos_no_banco' => $total_veiculos,
            'teste_busca_simples' => $teste_result,
            'cliente_id_filtrado' => $cliente_id
        ]);
        exit;
    }
    // 👆 FIM DO DEBUG 👆

    echo json_encode($resultados);

} catch (PDOException $e) {
    // Retorna o erro real do PDO
    echo json_encode([
        'erro' => true,
        'mensagem' => $e->getMessage(),
        'codigo' => $e->getCode(),
        'query' => $sql ?? 'N/A',
        'params' => $params ?? 'N/A'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'erro' => true,
        'mensagem' => $e->getMessage()
    ]);
}
?>
