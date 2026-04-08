<?php
ob_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../conexao.php';

// ✅ Buscar configurações
$config = $pdo->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$url_sistema = $config['url_sistema'] ?? 'http://localhost/OdontoClinic/';
$nome_sistema = $config['nome_sistema'] ?? 'Sistema';
$telefone_sistema = $config['telefone_sistema'] ?? '';
$endereco_sistema = $config['endereco_sistema'] ?? '';

// ✅ Garantir que o ano está sendo passado
$ano = isset($_GET['ano']) && !empty($_GET['ano']) ? intval($_GET['ano']) : date('Y');

// ✅ DEBUG - Remova depois
error_log("Ano no class: " . $ano);

// ✅ Passar via GET E também como variável direta
$_GET['ano'] = $ano;
$_GET['url_sistema'] = $url_sistema;
$_GET['nome_sistema'] = $nome_sistema;
$_GET['telefone_sistema'] = $telefone_sistema;
$_GET['endereco_sistema'] = $endereco_sistema;

// ✅ Incluir relatório
include __DIR__ . '/rel_bal_anual.php';
$html = ob_get_clean();

// ✅ 6. Configurar DomPDF para A4
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
$pdf->setPaper('A4', 'portrait');
$pdf->loadHtml($html);
$pdf->render();

// ✅ 7. Headers para download/visualização
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=balanco_anual_" . date('Y') . ".pdf");

$pdf->stream("balanco_anual_" . date('Y') . ".pdf", [
    "Attachment" => false
]);
exit;
?>