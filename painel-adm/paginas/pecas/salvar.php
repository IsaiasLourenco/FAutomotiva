<?php
session_start();
$tabela = 'pecas';
require_once("../../../conexao.php");

if (!isset($_SESSION['id_user'])) { echo "Acesso negado!"; exit; }

$id                = @$_POST['id'];
$nome_padrao       = trim($_POST['nome_padrao'] ?? '');
$categoria         = $_POST['categoria'] ?? '';
$marca_recomendada = $_POST['marca_recomendada_id'] ?? null;
$codigo_interno    = trim($_POST['codigo_interno'] ?? '');
$ativo             = $_POST['ativo'] ?? 'Sim';

if (empty($nome_padrao)) { echo "Preencha o nome da peça!"; exit; }

// Validação: nome único (exceto o próprio)
if (!empty($id) && $id != 0) {
    $stmt = $pdo->prepare("SELECT id FROM $tabela WHERE nome_padrao = :nome AND id != :id");
    $stmt->execute(['nome' => $nome_padrao, 'id' => $id]);
} else {
    $stmt = $pdo->prepare("SELECT id FROM $tabela WHERE nome_padrao = :nome");
    $stmt->execute(['nome' => $nome_padrao]);
}
if ($stmt->rowCount() > 0) { echo "Esta peça já está cadastrada!"; exit; }

try {
    if (!empty($id) && $id != 0) {
        // UPDATE
        $query = $pdo->prepare("UPDATE $tabela SET
            nome_padrao = :nome,
            categoria = :cat,
            marca_recomendada_id = :marca,
            codigo_interno = :cod,
            ativo = :ativo
            WHERE id = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
    } else {
        // INSERT
        $query = $pdo->prepare("INSERT INTO $tabela
            (nome_padrao, categoria, marca_recomendada_id, codigo_interno, ativo)
            VALUES
            (:nome, :cat, :marca, :cod, :ativo)");
    }

    $query->bindValue(':nome', $nome_padrao);
    $query->bindValue(':cat', $categoria);
    $query->bindValue(':marca', $marca_recomendada ?: null, $marca_recomendada ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $query->bindValue(':cod', $codigo_interno);
    $query->bindValue(':ativo', $ativo);

    $query->execute();
    echo "Salvo com Sucesso";

} catch (Exception $e) {
    echo "Erro ao salvar: " . $e->getMessage();
}
?>
