<?php
// ✅ 1. Buffer de saída (obrigatório para PDF)
ob_start();

// ✅ 2. Carregar dependências
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../conexao.php';

// ✅ 3. Receber parâmetros do formulário
$dataInicial       = $_POST['dataInicial'] ?? date('Y-m-01');
$dataFinal         = $_POST['dataFinal']   ?? date('Y-m-t');
$filtro_data       = $_POST['filtro_data'] ?? 'vencimento';
$filtro_tipo       = $_POST['filtro_tipo'] ?? 'receber';
$filtro_lancamento = $_POST['filtro_lancamento'] ?? '';
$filtro_pendente   = $_POST['filtro_pendente'] ?? '';

// ✅ 4. Mapear filtro_data para coluna do banco
$mapaColunas = [
    'lancamento' => 'data_lancamento',
    'vencimento' => 'data_vencimento',
    'pagamento'  => 'data_pagamento'
];
$colunaData = $mapaColunas[$filtro_data] ?? 'data_vencimento';

// ✅ 5. Decidir qual tabela e campos usar
if ($filtro_tipo === 'pagar') {
    $tabela = 'pagar';
    $campo_pessoa = 'fornecedor';
    $tabela_pessoa = 'fornecedores';
    $tipo_movimento = 'Saídas / Despesas';
    $cor_destaque = '#e74c3c';  // vermelho para despesas
    $cor_fundo = '#fadbd8';
} else {
    $tabela = 'receber';
    $campo_pessoa = 'paciente';
    $tabela_pessoa = 'pacientes';
    $tipo_movimento = 'Entradas / Ganhos';
    $cor_destaque = '#27ae60';  // verde para receitas
    $cor_fundo = '#d5f4e6';
}

// ✅ 6. Montar query base
$sql = "SELECT r.*, p.nome as pessoa_nome, fp.nome as forma_nome 
        FROM {$tabela} r
        LEFT JOIN {$tabela_pessoa} p ON r.{$campo_pessoa} = p.id
        LEFT JOIN forma_pagamento fp ON r.forma_pagamento = fp.id
        WHERE 1=1";
$params = [];

// ✅ 7. Filtro de período na coluna selecionada
if (!empty($dataInicial) && !empty($dataFinal)) {
    $sql .= " AND {$colunaData} IS NOT NULL 
              AND {$colunaData} != '' 
              AND {$colunaData} != '0000-00-00'
              AND {$colunaData} >= :data_inicial 
              AND {$colunaData} <= :data_final";
    $params[':data_inicial'] = $dataInicial;
    $params[':data_final'] = $dataFinal;
}

// ✅ 8. Filtro por tipo de lançamento (referencia)
if (!empty($filtro_lancamento)) {
    $sql .= " AND r.referencia = :referencia";
    $params[':referencia'] = $filtro_lancamento;
}

// ✅ 9. Filtro por status (pago/pendente)
if ($filtro_pendente === 'Sim') {
    $sql .= " AND r.data_pagamento IS NOT NULL 
              AND r.data_pagamento != '' 
              AND r.data_pagamento != '0000-00-00'";
} elseif ($filtro_pendente === 'Não') {
    $sql .= " AND (r.data_pagamento IS NULL 
                  OR r.data_pagamento = '' 
                  OR r.data_pagamento = '0000-00-00')";
}

$sql .= " ORDER BY r.{$colunaData} DESC";

// ✅ 10. Executar query
try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();
    $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $contas = [];
    error_log("Erro rel_fin: " . $e->getMessage());
}

// ✅ 11. Calcular totais e quantidades
$total_geral = 0;
$qtd_pago = 0;
$qtd_pendente = 0;
$total_pago = 0;
$total_pendente = 0;

foreach ($contas as $c) {
    $valor = $c['subtotal'] ?? $c['valor'] ?? 0;
    $total_geral += $valor;

    if (!empty($c['data_pagamento']) && $c['data_pagamento'] != '0000-00-00') {
        $qtd_pago++;
        $total_pago += $valor;
    } else {
        $qtd_pendente++;
        $total_pendente += $valor;
    }
}

// ✅ 12. Formatar datas para exibição
$dataInicialF = !empty($dataInicial) ? date('d/m/Y', strtotime($dataInicial)) : '...';
$dataFinalF = !empty($dataFinal) ? date('d/m/Y', strtotime($dataFinal)) : '...';

// ✅ 13. Texto do filtro de data
$texto_filtro_data = '';
if ($filtro_data === 'lancamento') $texto_filtro_data = 'Data de Lançamento';
elseif ($filtro_data === 'vencimento') $texto_filtro_data = 'Data de Vencimento';
elseif ($filtro_data === 'pagamento') $texto_filtro_data = 'Data de Pagamento';

// ✅ 14. Passar variáveis para o relatório (via GET)
$_GET['dataInicialF']      = $dataInicialF;
$_GET['dataFinalF']        = $dataFinalF;
$_GET['filtro_data']       = $texto_filtro_data;
$_GET['filtro_tipo']       = $tipo_movimento;
$_GET['filtro_lancamento'] = $filtro_lancamento ?: 'Todos';
$_GET['filtro_pendente']   = $filtro_pendente === 'Sim' ? 'Pagos' : ($filtro_pendente === 'Não' ? 'Pendentes' : 'Todos');
$_GET['total_geral']       = $total_geral;
$_GET['qtd_pago']          = $qtd_pago;
$_GET['qtd_pendente']      = $qtd_pendente;
$_GET['total_pago']        = $total_pago;
$_GET['total_pendente']    = $total_pendente;
$_GET['contas']            = $contas;
$_GET['tipo_movimento']    = $tipo_movimento;
$_GET['cor_destaque']      = $cor_destaque;
$_GET['cor_fundo']         = $cor_fundo;

// Configurações do sistema
$config = $pdo->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$_GET['nome_sistema']      = $config['nome_sistema'] ?? 'Sistema';
$_GET['endereco_sistema']  = $config['endereco_sistema'] ?? '';
$_GET['telefone_sistema']  = $config['telefone_sistema'] ?? '';

// ✅ 15. Incluir o relatório visual
include __DIR__ . '/rel_fin.php';

$html = ob_get_clean();

// ✅ 16. Configurar DomPDF e gerar PDF
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->setIsRemoteEnabled(true);
$options->set('defaultFont', 'DejaVu Sans');
$options->setIsPhpEnabled(true);

$pdf = new Dompdf($options);
$pdf->setPaper('A4', 'portrait');
$pdf->loadHtml($html);
$pdf->render();

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=relatorio_financeiro_" . date('Y-m-d') . ".pdf");
$pdf->stream("relatorio_financeiro_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
exit;
