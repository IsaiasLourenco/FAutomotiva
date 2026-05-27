<?php
require_once '../../conexao.php';
header('Content-Type: application/json');
$q = trim(strtoupper($_GET['q'] ?? ''));
if (strlen($q) < 3) { echo json_encode([]); exit; }
$stmt = $pdo->prepare("SELECT v.id, v.placa, v.modelo FROM veiculos v WHERE v.placa LIKE :q OR v.modelo LIKE :q ORDER BY v.placa LIMIT 12");
$stmt->execute(['q' => "%$q%"]);
echo json_encode($stmt->fetchAll());
