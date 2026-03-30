<?php
session_start();
require_once("../conexao.php");

// Verifica se está logado
if (!isset($_SESSION['id_user'])) {
    echo "Acesso negado!";
    exit;
}

// Recebe os dados do formulário
$id_user      = @$_POST['id-usuario'];
$nome         = $_POST['nome'];
$email        = $_POST['email'];
$cpf          = $_POST['cpf'];
$telefone     = $_POST['telefone'];
$cep          = $_POST['cep'];
$rua          = $_POST['rua'];
$numero       = $_POST['numero'];
$bairro       = $_POST['bairro'];
$cidade       = $_POST['cidade'];
$estado       = $_POST['estado'];
$senha        = trim($_POST['senha'] ?? '');
$conf_senha   = trim($_POST['conf-senha'] ?? '');
$nivel        = $_POST['nivel'];
$ativo        = $_POST['ativo'];

// Validações básicas
if (empty($nome) || empty($email) || empty($cpf)) {
    echo "Preencha os campos obrigatórios!";
    exit;
}

// Validação de senha: só processa se pelo menos UM campo foi preenchido
$senha_hash = null;
if (!empty($senha) || !empty($conf_senha)) {
    if (empty($senha) || empty($conf_senha)) {
        echo "Preencha ambos os campos de senha!";
        exit;
    }
    if ($senha !== $conf_senha) {
        echo "As senhas não conferem!";
        exit;
    }
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
}

// Validação de email único (exceto o próprio usuário)
$email_buscado = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
$email_buscado->bindValue(":email", $email);
$email_buscado->bindValue(":id", $id_user, PDO::PARAM_INT);
$email_buscado->execute();
if ($email_buscado->rowCount() > 0) {
    echo "Este email já está cadastrado para outro usuário!";
    exit;
}

// Validação de CPF único (exceto o próprio usuário)
$cpf_buscado = $pdo->prepare("SELECT id FROM usuarios WHERE cpf = :cpf AND id != :id");
$cpf_buscado->bindValue(":cpf", $cpf);
$cpf_buscado->bindValue(":id", $id_usuario, PDO::PARAM_INT);
$cpf_buscado->execute();
if ($cpf_buscado->rowCount() > 0) {
    echo "Este CPF já está cadastrado para outro usuário!";
    exit;
}

// Upload da foto (se enviado)
$foto_nome = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK && !empty($_FILES['foto']['name'])) {
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    $nome_arquivo = $_FILES['foto']['name'];
    $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));

    if (in_array($extensao, $extensoes_permitidas)) {
        // ✅ Usa o ID do usuário como nome da foto (sempre o mesmo!)
        $foto_nome = 'usuario_' . $id_usuario . '.' . $extensao;
        $pasta_destino = realpath(__DIR__ . '/images/perfil/');

        if ($pasta_destino && is_dir($pasta_destino)) {
            $caminho_destino = $pasta_destino . DIRECTORY_SEPARATOR . $foto_nome;

            // ✅ Move e sobrescreve automaticamente se já existir
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
                // Foto salva com sucesso (antiga foi sobrescrita)
            } else {
                error_log("Erro ao mover arquivo para: " . $caminho_destino);
                echo "Erro ao salvar a foto!";
                exit;
            }
        } else {
            echo "Pasta de upload não encontrada!";
            exit;
        }
    } else {
        echo "Formato de imagem não permitido!";
        exit;
    }
}

try {
    // UPDATE - Atualizar perfil do usuário

    if ($senha_hash !== null) {
        // Atualiza COM nova senha
        if (!empty($foto_nome)) {
            // Com foto nova
            $query = $pdo->prepare("UPDATE usuarios SET 
                nome = :nome, email = :email, cpf = :cpf, telefone = :telefone,
                cep = :cep, rua = :rua, numero = :numero, bairro = :bairro,
                cidade = :cidade, estado = :estado, senha = :senha,
                cargo = :cargo, ativo = :ativo, foto = :foto 
                WHERE id = :id");
            $query->bindValue(":foto", "$foto_nome");
        } else {
            // Sem foto nova
            $query = $pdo->prepare("UPDATE usuarios SET 
                nome = :nome, email = :email, cpf = :cpf, telefone = :telefone,
                cep = :cep, rua = :rua, numero = :numero, bairro = :bairro,
                cidade = :cidade, estado = :estado, senha = :senha,
                cargo = :cargo, ativo = :ativo 
                WHERE id = :id");
        }
        $query->bindValue(":senha", "$senha_hash");
    } else {
        // Atualiza SEM alterar senha
        if (!empty($foto_nome)) {
            $query = $pdo->prepare("UPDATE usuarios SET 
                nome = :nome, email = :email, cpf = :cpf, telefone = :telefone,
                cep = :cep, rua = :rua, numero = :numero, bairro = :bairro,
                cidade = :cidade, estado = :estado,
                cargo = :cargo, ativo = :ativo, foto = :foto 
                WHERE id = :id");
            $query->bindValue(":foto", "$foto_nome");
        } else {
            $query = $pdo->prepare("UPDATE usuarios SET 
                nome = :nome, email = :email, cpf = :cpf, telefone = :telefone,
                cep = :cep, rua = :rua, numero = :numero, bairro = :bairro,
                cidade = :cidade, estado = :estado,
                cargo = :cargo, ativo = :ativo 
                WHERE id = :id");
        }
    }

    // Bind dos campos comuns
    $query->bindValue(":id", "$id_usuario", PDO::PARAM_INT);
    $query->bindValue(":nome", "$nome");
    $query->bindValue(":email", "$email");
    $query->bindValue(":cpf", "$cpf");
    $query->bindValue(":telefone", "$telefone");
    $query->bindValue(":cep", "$cep");
    $query->bindValue(":rua", "$rua");
    $query->bindValue(":numero", "$numero");
    $query->bindValue(":bairro", "$bairro");
    $query->bindValue(":cidade", "$cidade");
    $query->bindValue(":estado", "$estado");
    $query->bindValue(":cargo", "$nivel", PDO::PARAM_INT);
    $query->bindValue(":ativo", "$ativo");

    $query->execute();

    echo "Editado com Sucesso";
} catch (Exception $e) {
    echo "Erro ao salvar: " . $e->getMessage();
}
