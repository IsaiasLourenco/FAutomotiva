<?php
session_start();
$tabela = 'fornecedores';
require_once("../../../conexao.php");

// Verifica se está logado
if (!isset($_SESSION['id_user'])) {
    echo "Acesso negado!";
    exit;
}

// Recebe os dados do formulário
$id           = @$_POST['id'];
$nome         = trim($_POST['nome'] ?? '');
$email        = trim($_POST['email'] ?? '');
$cnpj         = trim($_POST['cnpj'] ?? '');
$telefone     = trim($_POST['telefone'] ?? '');
$cep          = trim($_POST['cep'] ?? '');
$rua          = trim($_POST['rua'] ?? '');
$numero       = trim($_POST['numero'] ?? '');
$bairro       = trim($_POST['bairro'] ?? '');
$cidade       = trim($_POST['cidade'] ?? '');
$estado       = trim($_POST['estado'] ?? '');
$ativo        = $_POST['ativo'] ?? 'Sim';
$observacoes  = trim($_POST['observacoes'] ?? '');

// Validações básicas
if (empty($nome) || empty($cnpj)) {
    echo "Preencha Nome e CNPJ!";
    exit;
}

// Validação: CNPJ único (exceto o próprio registro)
if (!empty($id) && $id != 0) {
    $stmt = $pdo->prepare("SELECT id FROM $tabela WHERE cnpj = :cnpj AND id != :id");
    $stmt->execute(['cnpj' => $cnpj, 'id' => $id]);
} else {
    $stmt = $pdo->prepare("SELECT id FROM $tabela WHERE cnpj = :cnpj");
    $stmt->execute(['cnpj' => $cnpj]);
}
if ($stmt->rowCount() > 0) {
    echo "Este CNPJ já está cadastrado!";
    exit;
}

try {
    if (!empty($id) && $id != 0) {
        // UPDATE
        $query = $pdo->prepare("UPDATE $tabela SET
            nome = :nome,
            email = :email,
            cnpj = :cnpj,
            telefone = :telefone,
            cep = :cep,
            rua = :rua,
            numero = :numero,
            bairro = :bairro,
            cidade = :cidade,
            estado = :estado,
            ativo = :ativo,
            observacoes = :observacoes
            WHERE id = :id");
        $query->bindValue(":id", $id, PDO::PARAM_INT);
    } else {
        // INSERT
        $query = $pdo->prepare("INSERT INTO $tabela
            (nome, email, cnpj, telefone, cep, rua, numero, bairro, cidade, estado, ativo, observacoes, data_criacao)
            VALUES
            (:nome, :email, :cnpj, :telefone, :cep, :rua, :numero, :bairro, :cidade, :estado, :ativo, :observacoes, NOW())");
    }

    // Bind dos campos
    $query->bindValue(":nome", $nome);
    $query->bindValue(":email", $email);
    $query->bindValue(":cnpj", $cnpj);
    $query->bindValue(":telefone", $telefone);
    $query->bindValue(":cep", $cep);
    $query->bindValue(":rua", $rua);
    $query->bindValue(":numero", $numero);
    $query->bindValue(":bairro", $bairro);
    $query->bindValue(":cidade", $cidade);
    $query->bindValue(":estado", $estado);
    $query->bindValue(":ativo", $ativo);
    $query->bindValue(":observacoes", $observacoes);

    $query->execute();
    echo "Salvo com Sucesso";

} catch (Exception $e) {
    echo "Erro ao salvar: " . $e->getMessage();
}
?>
