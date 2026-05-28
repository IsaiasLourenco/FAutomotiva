<?php
require_once "../../conexao.php";
header('Content-Type: application/json');

$q = strtoupper(trim($_GET['q'] ?? ''));
$cliente_id = $_GET['cliente_id'] ?? null;

if (strlen($q) < 3 && !$cliente_id) { echo json_encode([]); exit; }

$sql = "SELECT v.id, v.placa, v.modelo, v.marca, v.motor, v.ano, v.km_atual, c.nome as cliente_nome
        FROM veiculos v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE v.ativo IS NULL OR v.ativo = 'Sim' AND (";

$params = [];
if ($q) {
    $sql .= "v.placa LIKE :q1 OR v.modelo LIKE :q2 OR v.marca LIKE :q3";
    $params['q1'] = "%$q%"; $params['q2'] = "%$q%"; $params['q3'] = "%$q%";
}
if ($cliente_id) {
    if ($q) $sql .= " OR ";
    $sql .= "v.cliente_id = :cid";
    $params['cid'] = $cliente_id;
}
$sql .= ") ORDER BY v.placa LIMIT 15";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
