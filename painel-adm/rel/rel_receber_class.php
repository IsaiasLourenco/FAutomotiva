<?php
// ✅ 1. Buffer de saída (obrigatório para PDF)
ob_start();

// ✅ 2. Carregar Composer e conexão
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../conexao.php';

// ✅ 3. Buscar configurações do banco
$config = $pdo->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$url_sistema = $config['url_sistema'] ?? 'http://localhost/OdontoClinic/';
$nome_sistema = $config['nome_sistema'] ?? 'Sistema';
$telefone_sistema = $config['telefone_sistema'] ?? '';
$multa_atraso = $config['multa_padrao'] ?? 2.00;
$juros_atraso = $config['juros_padrao'] ?? 0.33;

// ✅ 4. Receber filtros do POST
$dataInicial = $_POST['dataInicial'] ?? '';
$dataFinal = $_POST['dataFinal'] ?? '';
$pago = $_POST['pago'] ?? '';
$tipo_data = $_POST['tipo_data'] ?? 'vencimento';

// ✅ 5. Passar variáveis via GET para o relatório
$_GET['dataInicial'] = $dataInicial;
$_GET['dataFinal'] = $dataFinal;
$_GET['pago'] = $pago;
$_GET['tipo_data'] = $tipo_data;
$_GET['url_sistema'] = $url_sistema;
$_GET['nome_sistema'] = $nome_sistema;
$_GET['telefone_sistema'] = $telefone_sistema;
$_GET['multa_atraso'] = $multa_atraso;
$_GET['juros_atraso'] = $juros_atraso;
$_GET['data_hoje'] = date('Y-m-d');

// ✅ 6. Incluir relatório DIRETAMENTE
include __DIR__ . '/rel_receber.php';
$html = ob_get_clean();

// ✅ 7. Configurar DomPDF
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

// ✅ 8. Headers corretos para PDF
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=relatorio_receber_" . date('Y-m-d') . ".pdf");

$pdf->stream("relatorio_receber_" . date('Y-m-d') . ".pdf", [
    "Attachment" => false
]);
exit;