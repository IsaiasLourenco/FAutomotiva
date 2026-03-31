<?php
// ✅ Headers ANTES de qualquer output
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// ✅ Logs para debug (remove depois)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não mostra erro na tela, mas loga
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/erro-recuperar.log');

try {
    // ✅ Conexão com try-catch
    if (!file_exists(__DIR__ . '/conexao.php')) {
        throw new Exception('Arquivo conexao.php não encontrado!');
    }
    require_once(__DIR__ . '/conexao.php');

    if (!isset($pdo) || !$pdo) {
        throw new Exception('Conexão com banco não estabelecida ($pdo não definido)');
    }

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Email não informado!']);
        exit;
    }

    // ✅ Busca usuário
    $query = $pdo->prepare("SELECT id, email FROM usuarios WHERE email = :email LIMIT 1");
    $query->bindValue(":email", $email, PDO::PARAM_STR);
    $query->execute();
    $res = $query->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        // ✅ Por segurança, não revela se email existe ou não (opcional)
        echo json_encode(['status' => 'error', 'message' => 'Esse email não está cadastrado!']);
        exit;
    }

    // ✅ Gera token
    $token = bin2hex(random_bytes(32));
    $token_expira = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // ✅ Atualiza token no banco
    $upd = $pdo->prepare("UPDATE usuarios SET token = :token, token_expira = :expira WHERE email = :email");
    $upd->execute([
        ':token' => $token,
        ':expira' => $token_expira,
        ':email' => $email
    ]);

    // ✅ Monta link
    $base = !empty($url_sistema)
        ? rtrim($url_sistema, '/')
        : 'http://' . $_SERVER['HTTP_HOST'] . '/OdontoClinic'; // ✅ Ajuste para sua pasta

    $link = $base . '/alterar-senha.php?email=' . urlencode($email) . '&token=' . $token;

    // ✅ Resposta de sucesso
    echo json_encode([
        'status' => 'success',
        'message' => 'Link gerado com sucesso!',
        'link' => $link
    ]);
    exit;
} catch (Exception $e) {
    // ✅ Loga erro e retorna JSON seguro
    error_log('Erro recuperar-senha: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno: ' . $e->getMessage() // ✅ Em produção, use apenas 'Erro ao processar'
    ]);
    exit;
}
