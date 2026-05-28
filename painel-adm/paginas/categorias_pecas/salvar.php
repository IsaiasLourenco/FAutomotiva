<?php
session_start();
$tabela = 'categorias_pecas';
require_once("../../../conexao.php");

// Verifica se está logado
if (!isset($_SESSION['id_user'])) {
    echo "Acesso negado!";
    exit;
}

// Recebe os dados do formulário
$id          = $_POST['id'];
$nome        = $_POST['nome'];

try {
    if (!empty($id) && $id != 0) {
        // UPDATE - Atualizar registro existente
        $query = $pdo->prepare("UPDATE $tabela SET nome = :nome WHERE id = :id");
        $query->bindValue(":nome", "$nome");
        $query->bindValue(":id", "$id", PDO::PARAM_INT);
    } else {
        // INSERT - Cadastrar novo registro
        $query = $pdo->prepare("INSERT INTO $tabela
            (nome)
            VALUES
            (:nome)");
    }

    // Bind dos campos comuns
    $query->bindValue(":nome", "$nome");

    $query->execute();

    echo "Salvo com Sucesso";
} catch (Exception $e) {
    echo "Erro ao salvar: " . $e->getMessage();
}
