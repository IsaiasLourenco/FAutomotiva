<?php
session_start();
require_once("conexao.php");
$email = $_POST['email'];
$senha = $_POST['senha'];

$query = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
$query->bindValue(":email", "$email");
$query->execute();
$usuario = $query->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    if (password_verify($senha, $usuario['senha'])) {

        if ($usuario['ativo'] !== 'Sim') {
            echo '<script>window.alert("Usuário inativo! Entre em contato com o administrador.")</script>';
            echo '<script>window.location="index.php"</script>';
            exit;
        }

        $_SESSION['id_user']       = $usuario['id'];
        $_SESSION['nome_user']     = $usuario['nome'];
        $_SESSION['id_cargo_user'] = $usuario['cargo'];
        $id_cargo_user             = $usuario['cargo'];

        $cargo = $pdo->prepare("SELECT nome FROM cargos WHERE id = :id_cargo_user LIMIT 1");
        $cargo->bindValue(":id_cargo_user", "$id_cargo_user", PDO::PARAM_INT);
        $cargo->execute();
        $cargo_result = $cargo->fetch(PDO::FETCH_ASSOC);

        // ✅ Permite login de qualquer usuário ativo (ADM, Dentista, Recepcionista, etc.)
        $nome_usuario = $usuario['nome'];

        // Redireciona para o painel principal
        header("Location: painel-adm/");
        exit;
    } else {
        echo '<script>window.alert("Dados incorretos!")</script>';
        echo '<script>window.location="index.php"</script>';
        exit;
    }
} else {
    $conta_usuarios = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios");
    $conta_usuarios->execute();
    $total_usuarios = $conta_usuarios->fetch(PDO::FETCH_ASSOC)['total'];

    if ($total_usuarios == 0) {
        $verifica_cargo = $pdo->prepare("SELECT id FROM cargos WHERE nome = :nome LIMIT 1");
        $verifica_cargo->bindValue(":nome", "Administrador");
        $verifica_cargo->execute();
        $cargo_existente = $verifica_cargo->fetch(PDO::FETCH_ASSOC);

        if ($cargo_existente) {
            $cargo_fake = $cargo_existente['id'];
        } else {
            $insere_cargo = $pdo->prepare("INSERT INTO cargos (nome) VALUES (:nome)");
            $insere_cargo->bindValue(":nome", "Administrador");
            $insere_cargo->execute();

            $cargo_fake = $pdo->lastInsertId();
        }

        $nome_fake   = "Usuário";
        $email_fake  = "usuario@email.com";
        $senha_fake  = "123";
        $senha_hash  = password_hash($senha_fake, PASSWORD_DEFAULT);
        $telefone_fake = "(19) 99999-9999";
        $cep_fake    = "13843-184";
        $rua_fake    = "Mococa";
        $numero_fake = "880";
        $bairro_fake = "Itacolomy";
        $cidade_fake = "Mogi Guaçu";
        $estado_fake = "SP";
        $foto_fake   = "sem-foto.jpg";
        $ativo_fake  = 'Sim';

        $insert = $pdo->prepare("INSERT INTO usuarios 
                (nome, email, senha, cargo, telefone, cep, rua, numero, bairro, cidade, estado, foto, ativo, data_criacao) 
                VALUES 
                (:nome, :email, :senha, :cargo, :telefone, :cep, :rua, :numero, :bairro, :cidade, :estado, :foto, :ativo, NOW())");

        $insert->bindValue(":nome", "$nome_fake");
        $insert->bindValue(":email", "$email_fake");
        $insert->bindValue(":senha", "$senha_hash");
        $insert->bindValue(":cargo", "$cargo_fake", PDO::PARAM_INT); // ID dinâmico do Administrador
        $insert->bindValue(":telefone", "$telefone_fake");
        $insert->bindValue(":cep", "$cep_fake");
        $insert->bindValue(":rua", "$rua_fake");
        $insert->bindValue(":numero", "$numero_fake");
        $insert->bindValue(":bairro", "$bairro_fake");
        $insert->bindValue(":cidade", "$cidade_fake");
        $insert->bindValue(":estado", "$estado_fake");
        $insert->bindValue(":foto", "$foto_fake");
        // $insert->bindValue(":ativo", "$ativo_fake", PDO::PARAM_INT);
        $insert->bindValue(":ativo", $ativo_fake);
        $insert->execute();

        echo "Banco vazio! Usuário de teste criado automaticamente.<br>";
        echo "Use: <strong>usuario@email.com</strong> e senha <strong>123</strong> para acessar.<br>";
        echo "Cargo vinculado: <strong>Administrador</strong> (ID: $cargo_fake)<br><br>";
        echo '<a href="index.php">Voltar para o login</a>';
    } else {
        echo '<script>window.alert("Dados incorretos!")</script>';
        echo '<script>window.location="index.php"</script>';
        exit;
    }
}
