<?php
require_once("../../../conexao.php");

$tabela = 'pagar';
$id = $_POST['id'] ?? null;

if (empty($id)) {
    echo "ID inválido!";
    exit;
}

// Busca segura
$stmt = $pdo->prepare("SELECT arquivo FROM $tabela WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    echo "Registro não encontrado!";
    exit;
}

$arquivo = $res['arquivo'] ?? 'sem-foto.png';

$path = '../../images/pagar/' . $arquivo;

if ($arquivo != "sem-foto.png" && file_exists($path)) {
    unlink($path);
}

// Delete seguro
$stmt = $pdo->prepare("DELETE FROM $tabela WHERE id = :id");
$stmt->execute([':id' => $id]);

echo 'Excluído com Sucesso';