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
$nome         = $_POST['nome'] ?? '';
$email        = $_POST['email'] ?? '';
$cnpj         = $_POST['cnpj'] ?? '';
$telefone     = $_POST['telefone'] ?? '';
$cep          = $_POST['cep'] ?? '';
$rua          = $_POST['rua'] ?? '';
$numero       = $_POST['numero'] ?? '';
$bairro       = $_POST['bairro'] ?? '';
$cidade       = $_POST['cidade'] ?? '';
$estado       = $_POST['estado'] ?? '';
$ativo        = $_POST['ativo'] ?? '1';
$observacoes  = $_POST['obs'] ?? '';

// Validações básicas
if (empty($nome) || empty($cnpj) || empty($ativo)) {
    echo "Preencha os campos obrigatórios!";
    exit;
}

// ✅ Validação de CNPJ único (CNPJ é o identificador único, não email)
$cnpj_buscado = $pdo->prepare("SELECT id FROM $tabela WHERE cnpj = :cnpj");
$cnpj_buscado->bindValue(":cnpj", $cnpj);
$cnpj_buscado->execute();
$resultado_cnpj = $cnpj_buscado->fetch(PDO::FETCH_ASSOC);

if ($resultado_cnpj && $resultado_cnpj['id'] != $id) {
    echo "Este CNPJ já está cadastrado!";
    exit;
}

try {
    if (!empty($id) && $id != 0) {
        // ✅ UPDATE - Atualizar fornecedor existente
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
        // ✅ INSERT - Cadastrar novo fornecedor
        $query = $pdo->prepare("INSERT INTO $tabela 
            (nome, email, cnpj, telefone, cep, rua, numero, bairro, cidade, estado, ativo, observacoes, data_criacao) 
            VALUES 
            (:nome, :email, :cnpj, :telefone, :cep, :rua, :numero, :bairro, :cidade, :estado, :ativo, :observacoes, NOW())");
    }

    // ✅ Bind dos campos (COMUNS para INSERT e UPDATE)
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