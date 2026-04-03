<?php
require_once __DIR__ . '/conexao.php';

function telaMensagem($titulo, $mensagem, $tipo = 'danger') {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header text-center bg-<?php echo $tipo ?> text-white">
                        <h5 class="mb-0"><?php echo htmlspecialchars($titulo) ?></h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="mb-4"><?php echo $mensagem ?></p>
                        <a href="index.php" class="btn btn-<?php echo $tipo === 'success' ? 'success' : 'secondary' ?> w-100">Voltar ao login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}

$email = @$_GET['email'] ?? '';
$token = @$_GET['token'] ?? '';

if (empty($email) || empty($token)) {
    telaMensagem('Erro', 'Link inválido.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = @$_POST['nova_senha'] ?? '';
    $confirma_senha = @$_POST['confirma_senha'] ?? '';

    if (empty($nova_senha) || empty($confirma_senha)) {
        telaMensagem('Erro', 'Preencha todos os campos.', 'danger');
    }
    if ($nova_senha !== $confirma_senha) {
        telaMensagem('Erro', 'As senhas não conferem.', 'danger');
    }
    if (strlen($nova_senha) < 6) {
        telaMensagem('Erro', 'A senha deve ter pelo menos 6 caracteres.', 'danger');
    }

    // ✅ USA NOW() (não UTC_TIMESTAMP)
    $query = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND token = :token AND token_expira >= NOW() LIMIT 1");
    $query->execute([':email' => $email, ':token' => $token]);

    if ($query->rowCount() === 0) {
        telaMensagem('Erro', 'Link expirado ou inválido.', 'danger');
    }

    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE usuarios SET senha = :senha, token = NULL, token_expira = NULL WHERE email = :email AND token = :token");
    $upd->execute([':senha' => $senha_hash, ':email' => $email, ':token' => $token]);
    telaMensagem('Senha alterada', 'Sua senha foi alterada com sucesso!', 'success');
}

// ✅ USA NOW() (não UTC_TIMESTAMP)
$query = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND token = :token AND token_expira >= NOW() LIMIT 1");
$query->execute([':email' => $email, ':token' => $token]);

if ($query->rowCount() === 0) {
    telaMensagem('Erro', 'Link expirado ou inválido.', 'danger');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>Redefinir Senha</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email) ?>">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token) ?>">
                            <div class="mb-3">
                                <label>Nova Senha</label>
                                <input type="password" name="nova_senha" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label>Confirmar Senha</label>
                                <input type="password" name="confirma_senha" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Alterar Senha</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="index.php">Voltar ao login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>