<?php
require_once("../../../conexao.php");

$id_user = $_POST['id'];
$acao = $_POST['acao'];

if ($acao == 'desmarcar_todos') {
    // Remove todas as permissões do usuário
    $pdo->query("DELETE FROM permissoes WHERE usuario = '$id_user'");
} else {
    // Marca todas: primeiro remove, depois insere todas
    $pdo->query("DELETE FROM permissoes WHERE usuario = '$id_user'");
    
    $acessos = $pdo->query("SELECT id FROM acessos ORDER BY id ASC");
    $lista = $acessos->fetchAll(PDO::FETCH_ASSOC);
    
    for ($i = 0; $i < count($lista); $i++) {
        $idAcesso = $lista[$i]['id'];
        $pdo->query("INSERT INTO permissoes (usuario, permissao) VALUES ('$id_user', '$idAcesso')");
    }
}
?>