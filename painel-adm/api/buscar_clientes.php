<?php
require_once "../../conexao.php";
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("SELECT id, nome, telefone, cpf, cep, rua, numero, bairro, cidade, estado FROM clientes
                       WHERE ativo = 'Sim' AND (nome LIKE :q OR telefone LIKE :q OR cpf LIKE :q) ORDER BY nome LIMIT 15");
$stmt->execute(['q' => "%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
