<?php
// ✅ 1. Buffer de saída (obrigatório para PDF)
ob_start();

// ✅ 2. Carregar Composer e conexão
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../conexao.php';

// ✅ 3. Receber ID da conta
$id = $_POST['id'] ?? $_GET['id'] ?? 0;

// ✅ 4. Buscar configurações do sistema
$config = $pdo->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$url_sistema = $config['url_sistema'] ?? 'http://localhost/OdontoClinic/';
$nome_sistema = $config['nome_sistema'] ?? 'Sistema';
$telefone_sistema = $config['telefone_sistema'] ?? '';
$endereco_sistema = $config['endereco_sistema'] ?? '';

// ✅ 5. Passar variáveis via GET para o relatório
$_GET['id'] = $id;
$_GET['url_sistema'] = $url_sistema;
$_GET['nome_sistema'] = $nome_sistema;
$_GET['telefone_sistema'] = $telefone_sistema;
$_GET['endereco_sistema'] = $endereco_sistema;

// ✅ 6. Incluir relatório DIRETAMENTE
include __DIR__ . '/rel_recibo.php';
$html = ob_get_clean();

// ✅ 7. Configurar DomPDF para 80mm (impressora térmica)
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->setIsRemoteEnabled(true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('chroot', [
    realpath(__DIR__ . '/../../'),
    'C:/xampp/htdocs/OdontoClinic'
]);

$pdf = new Dompdf($options);

// ✅ Tamanho 80mm: largura=227px, altura=auto (ajusta ao conteúdo)
// Em pontos: 80mm = 226.77pt, altura mínima 400pt (~140mm)
$pdf->setPaper([0, 0, 227, 500], 'portrait');

$pdf->loadHtml($html);
$pdf->render();

// ✅ 8. Headers para download/visualização
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=recibo_" . date('Ymd_His') . ".pdf");

$pdf->stream("recibo_" . date('Ymd_His') . ".pdf", [
    "Attachment" => false
]);
exit;
?>