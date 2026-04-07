<?php
// ✅ 1. Buffer de saída (obrigatório para PDF)
ob_start();

// ✅ 2. Carregar dependências
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../conexao.php';

// ✅ 3. Receber parâmetros do formulário
$dataInicial       = $_POST['dataInicial'] ?? '';
$dataFinal         = $_POST['dataFinal']   ?? '';
$filtro_data       = $_POST['filtro_data'] ?? 'vencimento';
$filtro_tipo       = $_POST['filtro_tipo'] ?? '';  // "" = tudo, "receber", "pagar"
$filtro_lancamento = $_POST['filtro_lancamento'] ?? '';
$filtro_pendente   = $_POST['filtro_pendente'] ?? '';  // "", "pago", "pendente", "vencidas"

// ✅ 4. Mapear filtro_data para coluna do banco
$mapaColunas = [
    'lancamento' => 'data_lancamento',
    'vencimento' => 'data_vencimento',
    'pagamento'  => 'data_pagamento'
];
$colunaData = $mapaColunas[$filtro_data] ?? 'data_vencimento';

// ✅ 5. Configurações comuns
$hoje = date('Y-m-d');
$params = [];
$where_comum = "";

// ✅ Filtro de período (aplica em ambas as tabelas se UNION)
if (!empty($dataInicial) && !empty($dataFinal)) {
    $where_comum .= " AND {$colunaData} IS NOT NULL  
                      AND {$colunaData} != '0000-00-00'
                      AND {$colunaData} >= :data_inicial 
                      AND {$colunaData} <= :data_final";
    $params[':data_inicial'] = $dataInicial;
    $params[':data_final'] = $dataFinal;
}

// ✅ Filtro por tipo de lançamento (referencia)
if (!empty($filtro_lancamento)) {
    $validos = ['Conta', 'Parcela', 'Resíduo'];
    if (in_array($filtro_lancamento, $validos)) {
        $where_comum .= " AND r.referencia = :ref";
        $params[':ref'] = $filtro_lancamento;
    }
}

// ✅ Filtro por status (pago/pendente/vencidas)
if ($filtro_pendente === 'pago') {
    $where_comum .= " AND r.data_pagamento IS NOT NULL 
                      AND r.data_pagamento != '0000-00-00'";
} elseif ($filtro_pendente === 'pendente') {
    $where_comum .= " AND (r.data_pagamento IS NULL 
                          OR r.data_pagamento = '0000-00-00')
                      AND (r.data_vencimento IS NULL  
                           OR r.data_vencimento = '0000-00-00' 
                           OR r.data_vencimento >= :hoje)";
    $params[':hoje'] = $hoje;
} elseif ($filtro_pendente === 'vencidas') {
    $where_comum .= " AND (r.data_pagamento IS NULL 
                          OR r.data_pagamento = '0000-00-00')
                      AND r.data_vencimento IS NOT NULL 
                      AND r.data_vencimento != '0000-00-00'
                      AND r.data_vencimento < :hoje";
    $params[':hoje'] = $hoje;
}

// ✅ 6. Montar query: UNION se "Tudo", simples se filtrado
if (empty($filtro_tipo)) {
    // ✅ UNION: juntar receber e pagar
    $tipo_movimento = 'Entradas e Saídas';
    $cor_destaque = '#2980b9';
    $cor_fundo = '#d6eaf8';
    
    // Query para RECEBER
    $sql_receber = "SELECT r.id, r.descricao, r.valor, r.subtotal, r.data_vencimento, 
                           r.data_pagamento, r.data_lancamento, r.forma_pagamento,
                           r.referencia, r.frequencia, r.obs, r.arquivo,
                           r.usuario_lanc, r.usuario_pgto, r.multa, r.juros, 
                           r.desconto, r.taxa,
                           p.nome as pessoa_nome, fp.nome as forma_nome,
                           'receber' as tipo_tabela, 'Paciente' as label_pessoa
                    FROM receber r
                    LEFT JOIN pacientes p ON r.paciente = p.id
                    LEFT JOIN forma_pagamento fp ON r.forma_pagamento = fp.id
                    WHERE 1=1 {$where_comum}";
    
    // Query para PAGAR
    $sql_pagar = "SELECT r.id, r.descricao, r.valor, r.subtotal, r.data_vencimento, 
                         r.data_pagamento, r.data_lancamento, r.forma_pagamento,
                         r.referencia, r.frequencia, r.obs, r.arquivo,
                         r.usuario_lanc, r.usuario_pgto, r.multa, r.juros, 
                         r.desconto, r.taxa,
                         p.nome as pessoa_nome, fp.nome as forma_nome,
                         'pagar' as tipo_tabela, 'Fornecedor' as label_pessoa
                  FROM pagar r
                  LEFT JOIN fornecedores p ON r.fornecedor = p.id
                  LEFT JOIN forma_pagamento fp ON r.forma_pagamento = fp.id
                  WHERE 1=1 {$where_comum}";
    
    // ✅ UNION ALL (mantém ordem, mais rápido que UNION)
    $sql = "({$sql_receber}) UNION ALL ({$sql_pagar}) ORDER BY {$colunaData} DESC";
    
} else {
    // ✅ Query simples para uma tabela só
    if ($filtro_tipo === 'pagar') {
        $tabela = 'pagar';
        $campo_pessoa = 'fornecedor';
        $tabela_pessoa = 'fornecedores';
        $tipo_movimento = 'Saídas / Despesas';
        $cor_destaque = '#e74c3c';
        $cor_fundo = '#fadbd8';
    } else {
        $tabela = 'receber';
        $campo_pessoa = 'paciente';
        $tabela_pessoa = 'pacientes';
        $tipo_movimento = 'Entradas / Ganhos';
        $cor_destaque = '#27ae60';
        $cor_fundo = '#d5f4e6';
    }
    
    $sql = "SELECT r.*, p.nome as pessoa_nome, fp.nome as forma_nome, 
                   '{$tabela}' as tipo_tabela, 
                   '{$campo_pessoa}' as label_pessoa
            FROM {$tabela} r
            LEFT JOIN {$tabela_pessoa} p ON r.{$campo_pessoa} = p.id
            LEFT JOIN forma_pagamento fp ON r.forma_pagamento = fp.id
            WHERE 1=1 {$where_comum}
            ORDER BY {$colunaData} DESC";
}

// ✅ 7. Executar query
try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $contas = [];
    error_log("Erro rel_sin: " . $e->getMessage());
}

// ✅ 8. Calcular totais e quantidades
$total_geral = 0;
$qtd_pago = 0;
$qtd_pendente = 0;
$qtd_vencidas = 0;
$total_pago = 0;
$total_pendente = 0;
$total_vencidas = 0;

foreach ($contas as $c) {
    $valorBase = (!empty($c['subtotal']) && $c['subtotal'] > 0) ? $c['subtotal'] : ($c['valor'] ?? 0);
    $data_pagamento = $c['data_pagamento'] ?? null;
    $data_vencimento = $c['data_vencimento'] ?? null;
    
    if (!is_null($data_pagamento) && $data_pagamento != '0000-00-00') {
        $total_pago += $valorBase;
        $qtd_pago++;
    } elseif (!empty($data_vencimento) && $data_vencimento != '0000-00-00' 
              && strtotime($data_vencimento) !== false && $data_vencimento < $hoje) {
        $total_vencidas += $valorBase;
        $qtd_vencidas++;
    } else {
        $total_pendente += $valorBase;
        $qtd_pendente++;
    }
}
$total_geral = $total_pago + $total_pendente + $total_vencidas;

// ✅ 9. Formatar para exibição
$dataInicialF = !empty($dataInicial) ? date('d/m/Y', strtotime($dataInicial)) : '...';
$dataFinalF = !empty($dataFinal) ? date('d/m/Y', strtotime($dataFinal)) : '...';

$texto_filtro_data = match($filtro_data) {
    'lancamento' => 'Data de Lançamento',
    'vencimento' => 'Data de Vencimento',
    'pagamento'  => 'Data de Pagamento',
    default => 'Data de Vencimento'
};

$texto_filtro_pendente = match($filtro_pendente) {
    'pago' => 'Pagos',
    'pendente' => 'Pendentes',
    'vencidas' => 'Vencidas',
    default => 'Todos'
};

// ✅ 10. Passar TODAS as variáveis via $_GET para o visual
$_GET['dataInicialF']       = $dataInicialF;
$_GET['dataFinalF']         = $dataFinalF;
$_GET['filtro_data']        = $texto_filtro_data;
$_GET['filtro_tipo']        = $tipo_movimento;
$_GET['filtro_lancamento']  = !empty($filtro_lancamento) ? $filtro_lancamento : 'Todos';
$_GET['filtro_pendente']    = $texto_filtro_pendente;
$_GET['total_geral']        = $total_geral;
$_GET['qtd_pago']           = $qtd_pago;
$_GET['qtd_pendente']       = $qtd_pendente;
$_GET['qtd_vencidas']       = $qtd_vencidas;
$_GET['total_pago']         = $total_pago;
$_GET['total_pendente']     = $total_pendente;
$_GET['total_vencidas']     = $total_vencidas;
$_GET['contas']             = $contas;
$_GET['tipo_movimento']     = $tipo_movimento;
$_GET['cor_destaque']       = $cor_destaque;
$_GET['cor_fundo']          = $cor_fundo;

// Configurações do sistema + URL
$config = $pdo->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$_GET['nome_sistema']       = $config['nome_sistema'] ?? 'Sistema';
$_GET['endereco_sistema']   = $config['endereco_sistema'] ?? '';
$_GET['telefone_sistema']   = $config['telefone_sistema'] ?? '';
$_GET['instagram_sistema']  = $config['instagram_sistema'] ?? '';
$_GET['desenvolvedor']      = $config['desenvolvedor'] ?? '';
$_GET['site_dev']           = $config['site_dev'] ?? '';
$_GET['url_sistema']        = $config['url_sistema'] ?? 'https://odontoclinic.vetor256.com/';

// ✅ 11. Incluir o relatório visual
include __DIR__ . '/rel_sin.php';

$html = ob_get_clean();

// ✅ 12. Configurar DomPDF e gerar PDF
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->setIsRemoteEnabled(true);
$options->set('defaultFont', 'DejaVu Sans');
$options->setIsPhpEnabled(true);
// ✅ Permitir acesso à pasta de imagens
$options->set('chroot', [
    realpath(__DIR__ . '/../../'),
    'C:/xampp/htdocs/OdontoClinic',  // Ajuste para localhost
    //'https://odontoclinic.vetor256.com'  // Ajuste para hospedagem
]);

$pdf = new Dompdf($options);
$pdf->setPaper('A4', 'portrait');
$pdf->loadHtml($html);
$pdf->render();

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=relatorio_sintetico_" . date('Y-m-d') . ".pdf");
$pdf->stream("relatorio_sintetico_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
exit;