<?php
header('Content-Type: application/json; charset=UTF-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/conexao.php';

try {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'E-mail inválido.']);
        exit;
    }

    // Busca usuário
    $query = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = :email LIMIT 1");
    $query->execute([':email' => $email]);
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    // Mensagem genérica por segurança
    if (!$usuario) {
        echo json_encode(['status' => 'success', 'message' => 'Se o e-mail existir, você receberá o link.']);
        exit;
    }

    // Gera token seguro (64 caracteres)
    $token = bin2hex(random_bytes(32));
    $expira = date('Y-m-d H:i:s', strtotime('+2 hours'));

    // Salva token no banco
    $upd = $pdo->prepare("UPDATE usuarios SET token = :token, token_expira = :expira WHERE email = :email");
    $upd->execute([
        ':token' => $token,
        ':expira' => $expira,
        ':email' => $email
    ]);

    // Link correto (ajuste se estiver em subpasta)
    $link = "http://" . $_SERVER['HTTP_HOST'] . "/OdontoClinic/alterar-senha.php?email=" . urlencode($email) . "&token=" . $token;

    // Configura PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'isaiaslourenco2020@gmail.com';
    $mail->Password = 'akqq jzvz ndwd dvqg'; // ✅ Sua App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('isaiaslourenco2020@gmail.com', $nome_sistema ?? 'Sistema');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Recuperação de Senha - ' . ($nome_sistema ?? 'Sistema');
    $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2>Olá, " . htmlspecialchars($usuario['nome']) . "!</h2>
            <p>Você solicitou a recuperação de senha.</p>
            <p>Clique no link abaixo para redefinir sua senha:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='{$link}' style='background: #0d6efd; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Redefinir Senha</a>
            </p>
            <p>Ou copie e cole este link:</p>
            <p style='background: #f8f9fa; padding: 10px; border-radius: 4px; word-break: break-all;'>{$link}</p>
            <p><strong>⚠️ Este link expira em 2 horas.</strong></p>
            <p>Se você não solicitou esta recuperação, ignore este e-mail.</p>
        </body>
        </html>
    ";
    $mail->AltBody = "Redefina sua senha: {$link}";

    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Se o e-mail existir, você receberá o link.']);

} catch (Exception $e) {
    error_log("Erro PHPMailer: " . $mail->ErrorInfo);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao enviar e-mail. Verifique os logs.']);
}
?>