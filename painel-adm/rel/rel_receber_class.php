<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../conexao.php';

$dataInicial = $_POST['dataInicial'] ?? '';
$dataFinal = $_POST['dataFinal'] ?? '';
$pago = $_POST['pago'] ?? '';
$tipo_data = $_POST['tipo_data'] ?? 'vencimento';

$html = file_get_contents($url_sistema."painel-adm/rel/rel_receber.php?dataInicial=$dataInicial&dataFinal=$dataFinal&pago=$pago&tipo_data=$tipo_data");
use Dompdf\Dompdf;
use Dompdf\Options;

header("Content-Transfer-Encoding: binary");
header("Content-Type: image/png");
header("Content-Type: image/pdf");

$options = new Options();
$options->setIsRemoteEnabled(true); 
$pdf = new Dompdf($options);
$pdf->set_paper('A4', 'portrait');
$pdf->loadHtml($html);
$pdf->render();
$pdf->stream("relatorio_receber_" . date('Y-m-d') . ".pdf", [
    "Attachment" => false,
    "filename" => "relatorio_receber_" . date('Y-m-d') . ".pdf"
]);
?>