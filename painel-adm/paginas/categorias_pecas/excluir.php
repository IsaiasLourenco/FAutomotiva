<?php
require_once("../../../conexao.php");
$tabela = 'categorias_pecas';

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where id = '$id'");

$pdo->query("DELETE FROM $tabela WHERE id = '$id'");
echo 'Excluído com Sucesso';
