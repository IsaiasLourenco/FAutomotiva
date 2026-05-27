<?php
session_start();
$tabela = 'clientes';
require_once("../../../conexao.php");

// Verifica se está logado
if (!isset($_SESSION['id_user'])) {
    echo "Acesso negado!";
    exit;
}

// Recebe os dados do formulário
$id          = @$_POST['id'];
$nome        = $_POST['nome'] ?? '';
$email       = $_POST['email'] ?? '';
$telefone    = $_POST['telefone'] ?? '';
$cpf         = $_POST['cpf'] ?? '';
$cep         = $_POST['cep'] ?? '';
$rua         = $_POST['rua'] ?? '';
$numero      = $_POST['numero'] ?? '';
$bairro      = $_POST['bairro'] ?? '';
$cidade      = $_POST['cidade'] ?? '';
$estado      = $_POST['estado'] ?? '';

// Validações básicas
if (empty($nome) || empty($email) || empty($cpf)) {
    echo "Preencha os campos obrigatórios!";
    exit;
}

// Validação de CPF único (ignora o próprio paciente na edição)
$cpf_buscado = $pdo->prepare("SELECT id FROM $tabela WHERE cpf = :cpf AND id != :id_atual");
$cpf_buscado->bindValue(":cpf", $cpf);
$cpf_buscado->bindValue(":id_atual", $id ?: 0, PDO::PARAM_INT);
$cpf_buscado->execute();
if ($cpf_buscado->rowCount() > 0) {
    echo "Este CPF já está cadastrado!";
    exit;
}

// ✅ Upload da foto (se enviado)
$foto_nome = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK && !empty($_FILES['foto']['name'])) {
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    if (in_array($extensao, $extensoes_permitidas)) {
        // Nome único: evita conflito entre clientes
        $foto_nome = 'cliente_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extensao;

        // ✅ CAMINHO CORRIGIDO (adicionada a barra /)
        $pasta_destino = realpath(__DIR__ . '/../../images/clientes/');

        // Se a pasta não existir, tenta criá-la
        if (!$pasta_destino || !is_dir($pasta_destino)) {
            $pasta_destino = __DIR__ . '/../../images/clientes/';
            if (!file_exists($pasta_destino)) {
                mkdir($pasta_destino, 0755, true);
            }
            $pasta_destino = realpath($pasta_destino);
        }

        if ($pasta_destino && is_dir($pasta_destino)) {
            $caminho_destino = $pasta_destino . DIRECTORY_SEPARATOR . $foto_nome;
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
                echo "Erro ao salvar a foto! Verifique as permissões da pasta.";
                exit;
            }
        } else {
            echo "Pasta de upload não encontrada e não pôde ser criada!";
            exit;
        }
    } else {
        echo "Formato de imagem não permitido! Use JPG, PNG ou GIF.";
        exit;
    }
}

try {
    if (!empty($id) && $id != 0) {
        // ✅ UPDATE: Atualizar cliente existente
        if (!empty($foto_nome)) {
            // Com nova foto
            $query = $pdo->prepare("UPDATE $tabela SET
                nome = :nome, email = :email, telefone = :telefone, cpf = :cpf,
                cep = :cep, rua = :rua, numero = :numero, bairro = :bairro,
                cidade = :cidade, estado = :estado, foto = :foto
                WHERE id = :id");
            $query->bindValue(":foto", $foto_nome);
        } else {
            // Sem nova foto (mantém a atual)
            $query = $pdo->prepare("UPDATE $tabela SET
                nome = :nome, email = :email, telefone = :telefone, cpf = :cpf,
                cep = :cep, rua = :rua, numero = :numero, bairro = :bairro,
                cidade = :cidade, estado = :estado
                WHERE id = :id");
        }
        $query->bindValue(":id", $id, PDO::PARAM_INT);

    } else {
        // ✅ INSERT: Cadastrar novo cliente
        $foto_final = empty($foto_nome) ? "sem-foto.jpg" : $foto_nome;

        $query = $pdo->prepare("INSERT INTO $tabela
            (nome, email, cpf, telefone, cep, rua, numero, bairro, cidade, estado, foto)
            VALUES
            (:nome, :email, :cpf, :telefone, :cep, :rua, :numero, :bairro, :cidade, :estado, :foto)");
        $query->bindValue(":foto", $foto_final);
    }

    // Bind dos campos comuns (para UPDATE e INSERT)
    $query->bindValue(":nome", $nome);
    $query->bindValue(":email", $email);
    $query->bindValue(":cpf", $cpf);
    $query->bindValue(":telefone", $telefone);
    $query->bindValue(":cep", $cep);
    $query->bindValue(":rua", $rua);
    $query->bindValue(":numero", $numero);
    $query->bindValue(":bairro", $bairro);
    $query->bindValue(":cidade", $cidade);
    $query->bindValue(":estado", $estado);

    $query->execute();
    echo "Salvo com Sucesso";

} catch (Exception $e) {
    error_log("Erro salvar.php: " . $e->getMessage());
    echo "Erro ao salvar: " . $e->getMessage();
}
?>
