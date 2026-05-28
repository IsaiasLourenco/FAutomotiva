<?php
session_start();
$tabela = 'veiculos';
require_once("../../../conexao.php");

if (!isset($_SESSION['id_user'])) { echo "Acesso negado!"; exit; }

$id            = @$_POST['id'];
$cliente_id    = $_POST['cliente_id'] ?? '';
$placa         = strtoupper(trim($_POST['placa'] ?? ''));
$marca         = trim($_POST['marca'] ?? '');
$modelo        = trim($_POST['modelo'] ?? '');
$ano           = trim($_POST['ano'] ?? '');
$motor         = trim($_POST['motor'] ?? '');
$km_atual      = $_POST['km_atual'] ?? null;
$observacoes   = trim($_POST['observacoes'] ?? '');

// Validações básicas
if (empty($cliente_id) || empty($placa)) { echo "Preencha cliente e placa!"; exit; }

// Validação: placa única (exceto o próprio)
if (!empty($id) && $id != 0) {
    $stmt = $pdo->prepare("SELECT id FROM $tabela WHERE placa = :placa AND id != :id");
    $stmt->execute(['placa' => $placa, 'id' => $id]);
} else {
    $stmt = $pdo->prepare("SELECT id FROM $tabela WHERE placa = :placa");
    $stmt->execute(['placa' => $placa]);
}
if ($stmt->rowCount() > 0) { echo "Esta placa já está cadastrada!"; exit; }

try {
    if (!empty($id) && $id != 0) {
        // UPDATE
        $query = $pdo->prepare("UPDATE $tabela SET
            cliente_id = :cliente,
            placa = :placa,
            marca = :marca,
            modelo = :modelo,
            ano = :ano,
            motor = :motor,
            km_atual = :km,
            observacoes = :obs
            WHERE id = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
    } else {
        // INSERT
        $query = $pdo->prepare("INSERT INTO $tabela
            (cliente_id, placa, marca, modelo, ano, motor, km_atual, observacoes)
            VALUES
            (:cliente, :placa, :marca, :modelo, :ano, :motor, :km, :obs)");
    }

    $query->bindValue(':cliente', $cliente_id, PDO::PARAM_INT);
    $query->bindValue(':placa', $placa);
    $query->bindValue(':marca', $marca ?: null);
    $query->bindValue(':modelo', $modelo ?: null);
    $query->bindValue(':ano', $ano ?: null);
    $query->bindValue(':motor', $motor ?: null);
    $query->bindValue(':km', $km_atual ?: null, $km_atual ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $query->bindValue(':obs', $observacoes ?: null);

    $query->execute();
    echo "Salvo com Sucesso";

} catch (Exception $e) {
    echo "Erro ao salvar: " . $e->getMessage();
}
?>
