<?php
session_start();
$tabela = 'marcas';
require_once("../../../conexao.php");

// Verifica se está logado
if (!isset($_SESSION['id_user'])) {
    echo "Acesso negado!";
    exit;
}

// Recebe os dados do formulário
$id             = $_POST['id'] ?? '';
$nome           = trim($_POST['nome'] ?? '');
$nota_qualidade = $_POST['nota_qualidade'] ?? 5;
$ativo          = $_POST['ativo'] ?? 1;

// Validações básicas
if (empty($nome)) {
    echo "Preencha o nome da marca!";
    exit;
}

// Valida nota entre 0 e 10
$nota_qualidade = intval($nota_qualidade);
if ($nota_qualidade < 0) $nota_qualidade = 0;
if ($nota_qualidade > 10) $nota_qualidade = 10;

// Validação: nome único (exceto o próprio registro)
if (!empty($id) && $id != 0) {
    $nome_buscado = $pdo->prepare("SELECT id FROM $tabela WHERE nome = :nome AND id != :id");
    $nome_buscado->bindValue(":nome", $nome);
    $nome_buscado->bindValue(":id", $id, PDO::PARAM_INT);
} else {
    $nome_buscado = $pdo->prepare("SELECT id FROM $tabela WHERE nome = :nome");
    $nome_buscado->bindValue(":nome", $nome);
}
$nome_buscado->execute();
if ($nome_buscado->rowCount() > 0) {
    echo "Esta marca já está cadastrada!";
    exit;
}

try {
    if (!empty($id) && $id != 0) {
        // UPDATE
        $query = $pdo->prepare("UPDATE $tabela SET nome = :nome, nota_qualidade = :nota, ativo = :ativo WHERE id = :id");
        $query->bindValue(":id", $id, PDO::PARAM_INT);
    } else {
        // INSERT
        $query = $pdo->prepare("INSERT INTO $tabela (nome, nota_qualidade, ativo) VALUES (:nome, :nota, :ativo)");
    }

    $query->bindValue(":nome", $nome);
    $query->bindValue(":nota", $nota_qualidade, PDO::PARAM_INT);
    $query->bindValue(":ativo", $ativo, PDO::PARAM_INT);

    $query->execute();

    echo "Salvo com Sucesso";

} catch (Exception $e) {
    echo "Erro ao salvar: " . $e->getMessage();
}
?>
