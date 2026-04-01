<?php
session_start();
require_once("../../../conexao.php");

if (!isset($_SESSION['id_user'])) { echo "Erro: Acesso negado!"; exit; }

$id_arquivo = @$_POST['id'] ?? 0;
$id_conta = @$_POST['id_conta'] ?? 0;

if (!$id_arquivo || !$id_conta) { echo "Erro: Dados inválidos!"; exit; }

// ✅ Busca dados do arquivo (USANDO SUAS COLUNAS)
$stmt = $pdo->prepare("SELECT caminho_arquivo FROM arquivos_conta WHERE id = :id AND id_conta = :id_conta LIMIT 1");
$stmt->execute([':id' => $id_arquivo, ':id_conta' => $id_conta]);
$arquivo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$arquivo) { echo "Erro: Arquivo não encontrado!"; exit; }

// ✅ Remove do disco
$caminho = realpath(__DIR__ . '/../../images/arquivos/' . $arquivo['caminho_arquivo']);
if ($caminho && file_exists($caminho)) {
    unlink($caminho);
}

// ✅ Remove do banco
$stmt_del = $pdo->prepare("DELETE FROM arquivos_conta WHERE id = :id");
$stmt_del->execute([':id' => $id_arquivo]);

echo "Sucesso: Arquivo excluído!";
?>