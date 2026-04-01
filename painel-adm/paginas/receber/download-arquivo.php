<?php
require_once("../../../conexao.php");

$id = @$_GET['id'] ?? 0;
$acao = @$_GET['acao'] ?? 'visualizar';
$origem = @$_SERVER['HTTP_REFERER'] ?? '../../../receber.php';

if (!$id) {
    echo "Erro: ID inválido!";
    exit;
}

$stmt = $pdo->prepare("SELECT nome_arquivo, caminho_arquivo, tipo_arquivo FROM arquivos_conta WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$arq = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$arq) {
    echo "Erro: Arquivo não encontrado!";
    exit;
}

$caminho = realpath(__DIR__ . '/../../images/arquivos/' . $arq['caminho_arquivo']);
if (!$caminho || !file_exists($caminho)) {
    echo "Erro: Arquivo não existe!";
    exit;
}

$ext = strtolower($arq['tipo_arquivo']);
$nome = $arq['nome_arquivo'];

// ✅ Força download
if ($acao == 'baixar') {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($nome) . '"');
    header('Content-Length: ' . filesize($caminho));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    flush();
    readfile($caminho);

    // ✅ Redireciona de volta após o download
    echo '<script>window.close(); if(window.opener) window.opener.location.reload();</script>';
    exit;
}

// ✅ Visualizar (apenas imagens e PDF)
$mime_types = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp'
];

$mime = $mime_types[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($caminho));
header('Content-Disposition: inline; filename="' . basename($nome) . '"');
readfile($caminho);
exit;
