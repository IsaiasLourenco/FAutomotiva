<?php
require_once("../../conexao.php");
$dataInicial = $_POST['dataInicial'];
$dataFinal = $_POST['dataFinal'];
$pago = $_POST['pago'] ?? '';
echo "Data Inicial: $dataInicial<br>";
echo "Data Final: $dataFinal<br>";
echo "Pago: $pago";
?>