<?php
require_once("../../../conexao.php");
$tabela = 'clientes';

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$foto = $res[0]['foto'];

if ($foto != "sem-foto.jpg") {
    unlink('../../images/clientes/' . $foto);}

$pdo->query("DELETE FROM $tabela WHERE id = '$id'");
echo 'Excluído com Sucesso';
