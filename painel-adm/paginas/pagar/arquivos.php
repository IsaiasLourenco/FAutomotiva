<?php
session_start();
require_once("../../../conexao.php");

if (!isset($_SESSION['id_user'])) {
    echo "Erro: Acesso negado!";
    exit;
}

$id_conta = @$_POST['id_conta'] ?? 0;
$arquivo = @$_FILES['arquivo'] ?? null;

if (!$id_conta || !$arquivo) {
    echo "Erro: Dados inválidos!";
    exit;
}

// ✅ Validações
$extensoes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'doc', 'docx', 'zip', 'rar', 'txt'];
$ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
$tamanho_max = 5 * 1024 * 1024;

if (!in_array($ext, $extensoes)) {
    echo "Erro: Formato não permitido!";
    exit;
}
if ($arquivo['size'] > $tamanho_max) {
    echo "Erro: Arquivo muito grande (máx. 5MB)!";
    exit;
}
if ($arquivo['error'] !== UPLOAD_ERR_OK) {
    echo "Erro no upload: código " . $arquivo['error'];
    exit;
}

// ✅ Pasta de destino
$pasta = realpath(__DIR__ . '/../../images/arquivos/');
if (!$pasta || !is_dir($pasta)) {
    echo "Erro: Pasta de upload não encontrada!";
    exit;
}

// ✅ Nome único para o arquivo
$nome_unico = 'conta_' . $id_conta . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$caminho_destino = $pasta . DIRECTORY_SEPARATOR . $nome_unico;

if (!move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
    echo "Erro ao salvar arquivo!";
    exit;
}

// ✅ Salva no banco
$stmt = $pdo->prepare("INSERT INTO arquivos_conta (
    id_conta, nome_arquivo, tipo_arquivo, caminho_arquivo, data_upload, usuario_upload
) VALUES (:id_conta, :nome_orig, :tipo, :caminho, NOW(), :usuario)");

$stmt->execute([
    ':id_conta' => $id_conta,
    ':nome_orig' => $arquivo['name'],
    ':tipo' => $ext,
    ':caminho' => $nome_unico,
    ':usuario' => $_SESSION['id_user']
]);

echo "Sucesso: Arquivo anexado!";
