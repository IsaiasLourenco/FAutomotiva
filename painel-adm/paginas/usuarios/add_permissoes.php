<?php
require_once("../../../conexao.php");

$id_user = @$_POST['id'] ?? '';
$permissao = @$_POST['acesso'] ?? '';

if (empty($id_user) || empty($permissao)) {
    echo "Erro: dados inválidos";
    exit;
}

// Verifica se já existe
$verifica = $pdo->query("SELECT id FROM permissoes WHERE usuario = '$id_user' AND permissao = '$permissao'");

if ($verifica->rowCount() > 0) {
    // Remove (desmarca)
    $pdo->query("DELETE FROM permissoes WHERE usuario = '$id_user' AND permissao = '$permissao'");
    echo "removido";
} else {
    // Insere (marca)
    $pdo->query("INSERT INTO permissoes (usuario, permissao) VALUES ('$id_user', '$permissao')");
    echo "inserido";
}
?>