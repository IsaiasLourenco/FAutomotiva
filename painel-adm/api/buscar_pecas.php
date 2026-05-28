<?php
require_once "../../conexao.php";
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("SELECT p.id, p.nome_padrao, p.categoria, m.nome as marca_recomendada FROM pecas p
                       LEFT JOIN marcas m ON p.marca_recomendada_id = m.id WHERE p.ativo = 'Sim' AND p.nome_padrao LIKE :q ORDER BY p.nome_padrao LIMIT 15");
$stmt->execute(['q' => "%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
