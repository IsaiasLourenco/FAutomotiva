<?php
require_once("../../../conexao.php");
$tabela = 'receber';

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$arquivo = $res[0]['arquivo'];

if ($arquivo != "sem-foto.png") {
    unlink('../../images/receber/' . $arquivo);}

$pdo->query("DELETE FROM $tabela WHERE id = '$id'");
echo 'Excluído com Sucesso';
