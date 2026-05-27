<?php
// FAutomotiva/painel-adm/api/buscar_pecas.php
require_once '../../conexao.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("SELECT p.id, p.nome_padrao FROM pecas p JOIN pecas_sinonimos s ON s.peca_id = p.id WHERE p.ativo = 1
                       AND (p.nome_padrao LIKE :q1 OR s.nome_sinonimo LIKE :q2) GROUP BY p.id ORDER BY p.nome_padrao LIMIT 12");
$stmt->execute(['q1' => "%$q%", 'q2' => "%$q%"]);
echo json_encode($stmt->fetchAll());
