<?php
// ✅ 1. Aumentar limites ANTES de qualquer coisa
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

ob_start();

// ✅ 2. Definir raiz do projeto
$root_path = realpath(__DIR__ . '/../../');

// ✅ 3. Carregar Composer e conexão
require_once $root_path . '/vendor/autoload.php';
require_once $root_path . '/conexao.php';

// ✅ 4. Buscar configurações
$config = $pdo->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$url_sistema = $config['url_sistema'] ?? 'https://odontoclinic.vetor256.com/';
$nome_sistema = $config['nome_sistema'] ?? 'Sistema';
$telefone_sistema = $config['telefone_sistema'] ?? '';
$endereco_sistema = $config['endereco_sistema'] ?? '';
$instagram_sistema = $config['instagram_sistema'] ?? '';
$site_dev = $config['site_dev'] ?? '';
$desenvolvedor = $config['desenvolvedor'] ?? '';

// ✅ 5. Ano com validação
$ano_raw = $_GET['ano'] ?? '';
$ano = (is_numeric($ano_raw) && intval($ano_raw) > 1900 && intval($ano_raw) < 2100) ? intval($ano_raw) : date('Y');

// ✅ 6. Passar variáveis para o view
$vars = [
    'ano' => $ano,
    'url_sistema' => $url_sistema,
    'nome_sistema' => $nome_sistema,
    'telefone_sistema' => $telefone_sistema,
    'endereco_sistema' => $endereco_sistema,
    'instagram_sistema' => $instagram_sistema,
    'site_dev' => $site_dev,
    'desenvolvedor' => $desenvolvedor,
    'root_path' => $root_path
];
extract($vars);
include __DIR__ . '/rel_bal_anual.php';
$html = ob_get_clean();

// ✅ 7. Configurar DomPDF COM OTIMIZAÇÕES
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->setIsRemoteEnabled(true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('chroot', [$root_path]);
// ✅ Otimizações para reduzir consumo de memória
$options->set('enablePhp', false);
$options->set('enableJavascript', false);
$options->set('enableCssFloat', false);
$options->set('debugCss', false);
$options->set('debugLayout', false);

$pdf = new Dompdf($options);
$pdf->setPaper('A4', 'portrait');

// ✅ Aumentar memória novamente (garantia)
ini_set('memory_limit', '512M');

$pdf->loadHtml($html);
$pdf->render();

// ✅ Headers
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=balanco_anual_{$ano}.pdf");

$pdf->stream("balanco_anual_{$ano}.pdf", ["Attachment" => false]);
exit;
?>