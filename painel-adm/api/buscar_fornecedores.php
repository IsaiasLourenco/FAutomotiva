<?php
require_once '../../conexao.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

$stmt = $pdo->prepare("
    SELECT id, nome, telefone, whatsapp
    FROM fornecedores
    WHERE ativo = 'Sim' AND (nome LIKE :q OR telefone LIKE :q)
    ORDER BY nome LIMIT 15
");
$stmt->execute(['q' => "%$q%"]);
echo json_encode($stmt->fetchAll());
