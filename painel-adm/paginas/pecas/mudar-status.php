<?php
require_once("../../../conexao.php");
$tabela = 'pecas';

$id = $_POST['id'];
$acao = $_POST['acao'];

$query = $pdo->query("UPDATE $tabela SET ativo = '$acao' WHERE id = '$id'");
echo 'Alterado com Sucesso';
