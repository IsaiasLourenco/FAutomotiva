<?php
$tabela = 'pagar';

require_once("../../../conexao.php");

function js_escape($str)
{
    if ($str === null) return '';
    return str_replace(["'", "\\", "\n", "\r", '"'], ["\\'", "\\\\", "\\n", "\\r", '\"'], (string)$str);
}

$dataInicial = @$_POST['p1'] ?? '';
$dataFinal   = @$_POST['p2'] ?? '';
$pago        = @$_POST['p3'] ?? '';
$tipoData    = @$_POST['p4'] ?? '';

// Normaliza valores
if ($dataInicial === 'undefined' || $dataInicial === '') $dataInicial = '';
if ($dataFinal === 'undefined' || $dataFinal === '') $dataFinal = '';
if ($pago === 'undefined' || $pago === '') $pago = '';
if ($tipoData === 'undefined' || $tipoData === '') $tipoData = 'vencimento';

$mapaColunas = [
    'vencimento'   => 'data_vencimento',
    'pagamento'    => 'data_pagamento',
    'lancamento'   => 'data_lancamento'
];
$colunaData = $mapaColunas[$tipoData] ?? 'data_vencimento';
$hoje = date('Y-m-d');

// ✅ QUERY BASE
$query = "SELECT * FROM $tabela WHERE 1=1";
$params = [];

// ✅ FILTRO DE PERÍODO (SÓ ADICIONA SE DATAS FOREM PREENCHIDAS)
if (!empty($dataInicial) && !empty($dataFinal)) {
    $query .= " AND $colunaData >= :data_inicial AND $colunaData <= :data_final";
    $params[':data_inicial'] = $dataInicial;
    $params[':data_final'] = $dataFinal;
}

// ✅ FILTRO DE STATUS
if ($pago === 'pagas') {
    $query .= " AND data_pagamento IS NOT NULL";
} elseif ($pago === 'pendentes') {
    $query .= " AND data_vencimento >= :hoje
                AND data_pagamento IS NULL";
    $params[':hoje'] = $hoje;
} elseif ($pago === 'vencidas') {
    $query .= " AND data_vencimento < :hoje
                AND data_pagamento IS NULL";
    $params[':hoje'] = $hoje;
}

$query .= " ORDER BY id DESC";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);

// ✅ Inicializa totais
$total_pendentes    = 0;
$total_pago         = 0;
$total_vencidas     = 0;
$qtd_pendentes      = 0;
$qtd_pago           = 0;
$qtd_vencidas      = 0;

echo <<<HTML
<table class="table table-hover tabela-pequena" id="tabela">
    <thead> 
        <tr> 
            <th>Descrição</th>
            <th>Fornecedor</th>
            <th>Lançamento</th>
            <th>Vencimento</th>
            <th class="esc">Pagamento</th>
            <th>Valor</th>
            <th>Ações</th>
        </tr> 
    </thead> 
    <tbody>
HTML;

if ($linhas > 0) {
    for ($i = 0; $i < $linhas; $i++) {
        $id = $res[$i]['id'];
        $descricao = $res[$i]['descricao'];
        $fornecedor_id = $res[$i]['fornecedor'];
        $valor = $res[$i]['valor'];
        $data_vencimento = $res[$i]['data_vencimento'];
        $data_lancamento = $res[$i]['data_lancamento'];
        $data_pagamento = $res[$i]['data_pagamento'];
        $forma_pagamento_id = $res[$i]['forma_pagamento'];
        $frequencia_id = $res[$i]['frequencia'];
        $obs = $res[$i]['obs'];
        $arquivo = $res[$i]['arquivo'];
        $multa = $res[$i]['multa'] ?? 0;
        $juros = $res[$i]['juros'] ?? 0;
        $desconto = $res[$i]['desconto'] ?? 0;
        $taxa = $res[$i]['taxa'] ?? 0;
        $subtotal = $res[$i]['subtotal'] ?? 0;

        // ✅ Acumula totais corretamente usando subtotal (se existir)
        $valorBase = ($subtotal && $subtotal > 0) ? $subtotal : $valor;

        if (!is_null($data_pagamento) && $data_pagamento != '0000-00-00') {
            $total_pago += $valorBase;
            $qtd_pago++;
        } elseif (!empty($data_vencimento) && $data_vencimento < $hoje) {
            $total_vencidas += $valorBase;
            $qtd_vencidas++;
        } else {
            $total_pendentes += $valorBase;
            $qtd_pendentes++;
        }

        $valorF = 'R$ ' . number_format($valor, 2, ',', '.');
        $multaF = $multa > 0 ? 'R$ ' . number_format($multa, 2, ',', '.') : '-';
        $jurosF = $juros > 0 ? 'R$ ' . number_format($juros, 2, ',', '.') : '-';
        $descontoF = $desconto > 0 ? '- R$ ' . number_format($desconto, 2, ',', '.') : '-';
        $taxaF = $taxa > 0 ? 'R$ ' . number_format($taxa, 2, ',', '.') : '-';
        $subtotalF = 'R$ ' . number_format($subtotal, 2, ',', '.');
        $data_vencimentoF = (!empty($data_vencimento) && $data_vencimento != '0000-00-00') ? date('d/m/Y', strtotime($data_vencimento)) : '-';
        $data_lancamentoF = (!empty($data_lancamento) && $data_lancamento != '0000-00-00') ? date('d/m/Y', strtotime($data_lancamento)) : '-';
        $data_pagamentoF_tabela = (!empty($data_pagamento) && $data_pagamento != '0000-00-00') ? date('d/m/Y', strtotime($data_pagamento)) :
            '<span class="text-muted">Não pago</span>';
        $data_pagamentoF_js = (!empty($data_pagamento) && $data_pagamento != '0000-00-00') ? date('d/m/Y', strtotime($data_pagamento)) : 'Não pago';
        $data_vencimento_iso = (!empty($data_vencimento) && $data_vencimento != '0000-00-00') ? date('Y-m-d', strtotime($data_vencimento)) : '';
        $data_pagamento_iso = (!empty($data_pagamento) && $data_pagamento != '0000-00-00') ? date('Y-m-d', strtotime($data_pagamento)) : '';

        // ✅ LÓGICA CORRIGIDA: controle separado para cada botão
        if (!empty($data_pagamento) && $data_pagamento != '0000-00-00') {
            $classe_status = 'conta-paga';
            $mostrarBotaoBaixar = false;
            $mostrarBotaoRecibo = true;
        } elseif (!empty($data_vencimento) && $data_vencimento < date('Y-m-d')) {
            $classe_status = 'conta-vencida';
            $mostrarBotaoBaixar = true;
            $mostrarBotaoRecibo = false;
        } else {
            $classe_status = 'conta-nao-paga';
            $mostrarBotaoBaixar = true;
            $mostrarBotaoRecibo = false;
        }

        // ✅ Busca nome do fornecedor
        $qforn = $pdo->prepare("SELECT nome FROM fornecedores WHERE id = :id LIMIT 1");
        $qforn->bindValue(":id", $fornecedor_id, PDO::PARAM_INT);
        $qforn->execute();
        $fornecedor_nome = ($rforn = $qforn->fetch(PDO::FETCH_ASSOC)) ? $rforn['nome'] : 'Fornecedor Desconhecido';

        // ✅ Busca nome da forma de pagamento
        $qfp = $pdo->prepare("SELECT nome FROM forma_pagamento WHERE id = :id LIMIT 1");
        $qfp->bindValue(":id", $forma_pagamento_id, PDO::PARAM_INT);
        $qfp->execute();
        $forma_pagamento_nome = ($rfp = $qfp->fetch(PDO::FETCH_ASSOC)) ? $rfp['nome'] : 'Forma de Pagamento Desconhecida';

        // ✅ Busca nome da frequência
        $qfreq = $pdo->prepare("SELECT frequencia FROM frequencias WHERE id = :id LIMIT 1");
        $qfreq->bindValue(":id", $frequencia_id, PDO::PARAM_INT);
        $qfreq->execute();
        $frequencia_nome = ($rfreq = $qfreq->fetch(PDO::FETCH_ASSOC)) ? $rfreq['frequencia'] : 'Frequência Desconhecida';

        // ✅ Busca usuário que lançou
        $usuario_lanc_id = $res[$i]['usuario_lanc'] ?? null;
        $usuario_lanc_nome = 'Não informado';
        if ($usuario_lanc_id) {
            $qu = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id LIMIT 1");
            $qu->execute([":id" => $usuario_lanc_id]);
            $ru = $qu->fetch(PDO::FETCH_ASSOC);
            $usuario_lanc_nome = $ru['nome'] ?? 'Não encontrado';
        }

        // ✅ Busca usuário que pagou
        $usuario_pgto_id = $res[$i]['usuario_pgto'] ?? null;
        $usuario_pgto_nome = 'Não pago';
        if ($usuario_pgto_id) {
            $qu2 = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id LIMIT 1");
            $qu2->execute([":id" => $usuario_pgto_id]);
            $ru2 = $qu2->fetch(PDO::FETCH_ASSOC);
            $usuario_pgto_nome = $ru2['nome'] ?? 'Não encontrado';
        }

        // ✅ Formata valores para exibição (FAZER ANTES DO HEREDOC)
        $e_descricao = js_escape($descricao);
        $e_fornecedor_nome = js_escape($fornecedor_nome);
        $e_valorF = js_escape($valorF);
        $e_multaF = js_escape($multaF);
        $e_jurosF = js_escape($jurosF);
        $e_descontoF = js_escape($descontoF);
        $e_taxaF = js_escape($taxaF);
        $e_subtotalF = js_escape($subtotalF);
        $e_data_vencimentoF = js_escape($data_vencimentoF);
        $e_data_lancamentoF = js_escape($data_lancamentoF);
        $e_data_pagamentoF_js = js_escape($data_pagamentoF_js);
        $e_data_vencimento_iso = js_escape($data_vencimento_iso);
        $e_data_pagamento_iso = js_escape($data_pagamento_iso);
        $e_forma_pagamento_nome = js_escape($forma_pagamento_nome);
        $e_frequencia_nome = js_escape($frequencia_nome);
        $e_obs = js_escape($obs);
        $e_arquivo = js_escape($arquivo);
        $e_usuario_lanc_nome = js_escape($usuario_lanc_nome);
        $e_usuario_pgto_nome = js_escape($usuario_pgto_nome);

        echo <<<HTML
            <tr class="{$classe_status}">
                <td>
                    <input type="checkbox" id="seletor-{$id}" class="form-check-input" onchange="selecionar('{$id}')"> 
                    {$descricao}
                </td>
                <td>{$fornecedor_nome}</td>
                <td>{$data_lancamentoF}</td>
                <td>{$data_vencimentoF}</td>
                <td class="esc">{$data_pagamentoF_tabela}</td>
                <td>
                    <b>{$valorF}</b>
HTML;

        // ✅ Exibe "Pago" APENAS se a conta estiver efetivamente paga
        if (!empty($data_pagamento) && $data_pagamento != '0000-00-00' && $subtotal > 0 && $subtotal != $valor) {
            echo <<<HTML
                    <br>
                    <small class="text-success font-weight-bold">
                    Pago: {$subtotalF}
                    </small>
HTML;
        }
        echo <<<HTML
                </td>
                
                <td>
                    <a href="#" onclick="editar('{$id}','{$e_descricao}','{$fornecedor_id}','{$e_valorF}','{$e_data_vencimento_iso}','{$e_data_lancamentoF}','{$e_data_pagamento_iso}','{$forma_pagamento_id}','{$frequencia_id}','{$e_obs}','{$e_arquivo}','{$e_multaF}','{$e_jurosF}','{$e_descontoF}','{$e_taxaF}','{$e_subtotalF}')" title="Editar Dados">
                        <i class="fa fa-edit text-primary ico-grande"></i>
                    </a>
                    <li class="dropdown head-dpdn2" style="display: inline-block;">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Excluir Registro">
                            <i class="fa-solid fa-trash-can text-danger ico-grande"></i>
                        </a>
                        <ul class="dropdown-menu" style="margin-left:-230px;">
                            <li>
                                <div class="notification_desc2 centro">
	                                <p>Confirmar Exclusão? <br>
		                                <a href="#" onclick="excluir('{$id}')" class="btn btn-danger btn-xs">
			                                <span>Sim</span>
		                                </a>
	                                </p>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <a href="#" onclick="mostrar('{$e_descricao}',
                                                 '{$e_fornecedor_nome}',
                                                 '{$e_valorF}',
                                                 '{$e_data_vencimentoF}',
                                                 '{$e_data_lancamentoF}',
                                                 '{$e_data_pagamentoF_js}',
                                                 '{$e_forma_pagamento_nome}',
                                                 '{$e_frequencia_nome}',
                                                 '{$e_obs}',
                                                 '{$e_arquivo}',
                                                 '{$e_multaF}',
                                                 '{$e_jurosF}',
                                                 '{$e_descontoF}',
                                                 '{$e_taxaF}',
                                                 '{$e_subtotalF}',
                                                 '{$e_usuario_lanc_nome}',
                                                 '{$e_usuario_pgto_nome}')" title="Mostrar Dados">
                        <i class="fa fa-info-circle text-dark ico-grande"></i>
                    </a>
HTML;
                    // ✅ Verifica se esta conta tem parcelas ou resíduos vinculados
                    $stmt_relacionados = $pdo->prepare("SELECT COUNT(*) as total FROM pagar WHERE id_referencia = :id AND referencia IN ('Parcela', 'Resíduo')");
                    $stmt_relacionados->execute([':id' => $id]);
                    $tem_relacionados = $stmt_relacionados->fetchColumn() > 0;

                    if ($tem_relacionados) {
echo <<<HTML
                        <a href="#" onclick="mostrarRelacionadosPagar('{$id}', '{$e_descricao}')" title="Ver Parcelas e Resíduos">
                            <i class="fa-solid fa-diagram-project text-dark ico-grande"></i>
                        </a>
HTML;
                    }
                    // ✅ Dentro do bloco de ações, após o botão de editar:
                    if ($mostrarBotaoBaixar) {
echo <<<HTML
                        <a href="#" onclick="parcelarPagar('{$id}', 
                                                           '{$e_valorF}', 
                                                           '{$e_descricao}', 
                                                           '{$e_multaF}', 
                                                           '{$e_jurosF}', 
                                                           '{$e_descontoF}')" 
                                    data-debug-id="{$id}"
                                    title="Parcelar valor"> 
                            <i class="fa-solid fa-calendar-days text-success ico-grande"></i>
                        </a>
HTML;
                    }

            // ✅ Botão de baixar (só aparece se não pago)
            if ($mostrarBotaoBaixar) {
echo <<<HTML
                <input type="checkbox" class="check-baixar maozinha" data-id="{$id}" data-valor="{$valor}" title="Selecionar para baixa">
                <a href="#" onclick="baixarPagar('{$id}', 
                                                 '{$e_valorF}', 
                                                 '{$e_descricao}', 
                                                 '{$e_forma_pagamento_nome}', 
                                                 '{$data_vencimento_iso}')" title="Baixar valor">
                    <i class="fa-solid fa-square-check text-danger ico-grande"></i>
                </a>
HTML;
            }

            // ✅ Botão de recibo (só aparece se pago)
            if ($mostrarBotaoRecibo) {
echo <<<HTML
                <form method="POST" action="rel/rel_recibo_pagamento_class.php" target="_blank" style="display:inline-block">
                    <input type="hidden" name="id" value="{$id}">
                    <button title="Impressão Comprovante" class="btn-imprime">
                        <i class="fa fa-print cinza ico-grande"></i>
                    </button>
                </form>
HTML;
            }

            // ✅ Botão para abrir modal de arquivos
echo <<<HTML
            <a href="#" onclick="abrirArquivos('{$id}', '{$e_descricao}')" title="Arquivos">
                <i class="fa-solid fa-paperclip text-secondary ico-grande"></i>
            </a>
        </td>
    </tr>
HTML;
    }
}

// ✅ Formata totais para exibição
$total_pendentesF = number_format($total_pendentes, 2, ',', '.');
$total_pagoF = number_format($total_pago, 2, ',', '.');
$total_vencidasF = number_format($total_vencidas, 2, ',', '.');
$qtd_pendentesF = str_pad($qtd_pendentes, 2, '0', STR_PAD_LEFT);
$qtd_pagoF = str_pad($qtd_pago, 2, '0', STR_PAD_LEFT);
$qtd_vencidasF = str_pad($qtd_vencidas, 2, '0', STR_PAD_LEFT);

echo <<<HTML
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end negrito">
                    <span class="text-warning">Qtd Vencidas: {$qtd_vencidasF}</span> 
                        &nbsp;&nbsp;|&nbsp;&nbsp; 
                    <span class="text-warning">$ Vencidas: R$ {$total_vencidasF}</span> 
                        &nbsp;&nbsp;|&nbsp;&nbsp; 
                    <span class="text-danger">Qtd Pendentes: {$qtd_pendentesF}</span>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                    <span class="text-danger">$ Pendentes: R$ {$total_pendentesF}</span> 
                        &nbsp;&nbsp;|&nbsp;&nbsp; 
                    <span class="text-success">Qtd Pagas: {$qtd_pagoF}</span>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                    <span class="text-success">$ Pagas: R$ {$total_pagoF}</span>
                </td>
            </tr>
        </tfoot>
    </table>
<div class="row mt-2">
    <div class="col-md-3 col-sm-12 mb-2 mb-md-0">
        <label class="small text-muted mb-1">&nbsp;</label>
        <span class="d-inline-flex align-items-center gap-1 text-nowrap filtro-rapido">
            <span class="textoFiltroData">Filtrar por data de:</span>
            <a href="#" onclick="porData('vencimento')" class="text-decoration-none small">Vencimento</a>
            <span class="text-muted">|</span>
            <a href="#" onclick="porData('pagamento')" class="text-decoration-none small">Pagamento</a>
            <span class="text-muted">|</span>
            <a href="#" onclick="porData('lancamento')" class="text-decoration-none small">Lançamento</a>
        </span>
    </div>
    <input type="hidden" name="tipoData" id="tipoData" value="vencimento">
</div>
HTML;
?>

<style>
    .conta-paga {
        background-color: #e8f5e9 !important;
        border-left: 4px solid #4caf50 !important;
    }

    .conta-nao-paga {
        background-color: #ffebee !important;
        border-left: 4px solid #f44336 !important;
    }

    .conta-vencida {
        background-color: #ffcdd2 !important;
        border-left: 4px solid #d32f2f !important;
        font-weight: 600;
    }

    .conta-paga:hover,
    .conta-nao-paga:hover,
    .conta-vencida:hover {
        filter: brightness(0.95);
    }
</style>

<script>
    $(document).ready(function() {
        $('#btn-deletar').hide();
        var table = $('#tabela').DataTable({
            "ordering": false,
            "stateSave": true,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json",
                "emptyTable": "Nenhum Registro Encontrado!"
            },
            "columnDefs": [{
                "className": "dt-center",
                "targets": "_all"
            }]
        });
        $('#tabela_wrapper').addClass('tabela-pequena');
    });
</script>

<script type="text/javascript">
    // ✅ Função editar (igual ao receber, mas com fornecedor)
    function editar(id, descricao, fornecedor, valor, data_vencimento, data_lancamento, data_pagamento, forma_pagamento, frequencia, obs, arquivo, multa, juros, desconto, taxa, subtotal) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Registro');
        $('#id').val(id);
        $('#descricao-perfil').val(descricao);
        $('#fornecedor-perfil').val(fornecedor);
        $('#valor-conta').val(valor);
        $('#vencimento-conta').val(data_vencimento);
        $('#pagamento-conta').val(data_pagamento);
        $('#forma_pagamento').val(forma_pagamento);
        $('#frequencia').val(frequencia);
        $('#obs-perfil').val(obs);
        if (typeof $('#multa-perfil').val !== 'undefined') $('#multa-perfil').val(multa !== '-' ? multa : '');
        if (typeof $('#juros-perfil').val !== 'undefined') $('#juros-perfil').val(juros !== '-' ? juros : '');
        if (typeof $('#desconto-perfil').val !== 'undefined') $('#desconto-perfil').val(desconto !== '-' ? desconto.replace('- R$ ', '-') : '');
        if (typeof $('#taxa-perfil').val !== 'undefined') $('#taxa-perfil').val(taxa !== '-' ? taxa : '');
        if (arquivo && arquivo !== 'sem-foto.png') {
            $('#target-arquivo').attr("src", "./images/pagar/" + arquivo);
        } else {
            $('#target-arquivo').attr("src", "./images/pagar/sem-foto.png");
        }
        $('#modalForm').modal('show');
    }

    // ✅ Função mostrar (igual ao receber, mas com fornecedor)
    function mostrar(descricao, fornecedor, valor, data_vencimento, data_lancamento, data_pagamento, forma_pagamento, frequencia, obs, arquivo, multa, juros, desconto, taxa, subtotal, usuario_lanc, usuario_pgto) {
        $('#titulo_dados').text('Detalhes: ' + descricao);
        $('#descricao_dados-cli').text(descricao);
        $('#fornecedor_dados-cli').text(fornecedor);
        $('#valor_dados-cli').text(valor);
        $('#vencimento_dados-cli').text(data_vencimento && data_vencimento !== '-' ? data_vencimento : '-');
        $('#lancamento_dados-cli').text(data_lancamento && data_lancamento !== '-' ? data_lancamento : '-');
        $('#pagamento_dados-cli').text(data_pagamento && data_pagamento !== 'Não pago' ? data_pagamento : 'Não pago');
        $('#forma_pagamento_dados-cli').text(forma_pagamento);
        $('#frequencia_dados-cli').text(frequencia);
        $('#obs_dados-cli').text(obs || '-');
        $('#multa_dados-cli').text(multa);
        $('#juros_dados-cli').text(juros);
        $('#desconto_dados-cli').text(desconto);
        $('#taxa_dados-cli').text(taxa);
        $('#subtotal_dados-cli').text(subtotal);
        if (arquivo && arquivo !== 'sem-foto.png' && arquivo !== '') {
            $('#target-arquivo-dados').attr('src', './images/pagar/' + arquivo).show();
            $('#link-arquivo-dados').attr('href', './images/pagar/' + arquivo).show();
        } else {
            $('#target-arquivo-dados').attr('src', './images/pagar/sem-foto.png');
            $('#link-arquivo-dados').hide();
        }
        $('#usuario_lanc_dados-cli').text(usuario_lanc);
        $('#usuario_pgto_dados-cli').text(usuario_pgto);
        $('#modalDados').modal('show');
    }

    function limparCampos() {
        $('#id, #descricao-perfil, #fornecedor-perfil, #valor-conta, #vencimento-conta, #pagamento-conta, #forma_pagamento, #frequencia, #obs-perfil').val('');
        if (typeof $('#multa-perfil').val !== 'undefined') $('#multa-perfil, #juros-perfil, #desconto-perfil, #taxa-perfil').val('');
        $('#arquivo-conta').val('');
        $('#target-arquivo').attr('src', './images/pagar/sem-foto.png');
        $('#mensagem').text('').removeClass('text-danger');
    }

    function selecionar(id) {
        var ids = $('#ids').val();
        if ($('#seletor-' + id).is(":checked")) {
            $('#ids').val(ids + id + '-');
        } else {
            $('#ids').val(ids.replace(id + '-', ''));
        }
        $('#btn-deletar').toggle($('#ids').val() !== "");
    }

    function deletarSel() {
        var ids = $('#ids').val().split("-");
        for (var i = 0; i < ids.length - 1; i++) {
            if (ids[i]) excluir(ids[i]);
        }
        limparCampos();
    }

    function excluir(id) {
        $.ajax({
            url: 'paginas/' + pag + "/excluir.php",
            method: 'POST',
            data: {
                id: id
            },
            dataType: "html",
            success: function(mensagem) {
                if (mensagem.trim() === "Excluído com Sucesso") {
                    listar();
                } else {
                    $('#mensagem-excluir').addClass('text-danger').text(mensagem);
                }
            }
        });
    }

    // ✅ Função baixar individual (PAGAR)
    function baixarPagar(id, valor, descricao, forma_pgto, data_vencimento) {
        $('#id-baixar').val(id);
        $('#descricao-baixar').text(descricao);
        $('#valor-baixar').val(valor);
        if (forma_pgto && forma_pgto !== 'undefined') {
            $('#saida-baixar').val(forma_pgto).change();
        }
        $('#subtotal-baixar').val(valor);
        $('#valor-juros, #valor-desconto, #valor-multa, #valor-taxa').val('');
        $('#data-vencimento-baixar').val(data_vencimento);
        $('#modalBaixar').modal('show');
        $('#mensagem-baixar').text('');
    }

    // ✅ Array para baixa múltipla (igual ao receber)
    var idsBaixarSelecionados = [];

    // ✅ Evento para checkboxes de baixa (igual ao receber)
    $(document).on('change', '.check-baixar', function() {
        var id = $(this).data('id');
        var valor = parseFloat($(this).data('valor')) || 0;
        if ($(this).is(':checked')) {
            if (!idsBaixarSelecionados.some(item => item.id === id)) {
                idsBaixarSelecionados.push({
                    id: id,
                    valor: valor
                });
            }
        } else {
            idsBaixarSelecionados = idsBaixarSelecionados.filter(item => item.id !== id);
        }
        var total = idsBaixarSelecionados.reduce((sum, item) => sum + item.valor, 0);
        if (idsBaixarSelecionados.length > 0) {
            document.getElementById('btn-baixar').style.display = 'inline-block';
            document.getElementById('totalContas').innerText = 'R$ ' + total.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        } else {
            document.getElementById('btn-baixar').style.display = 'none';
            document.getElementById('totalContas').innerText = '';
        }
    });

    // ✅ Função para abrir modal de arquivos
    function abrirArquivos(id, descricao) {
        $('#titulo-arquivos').text(descricao);
        $('#id-conta-arquivos').val(id);
        $('#lista-arquivos').html('<p class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Carregando...</p>');
        $('#mensagem-arquivo').text('');
        $('#arquivo-adicional').val('');
        carregarListaArquivos(id);
        $('#modalArquivos').modal('show');
    }

    function carregarListaArquivos(id) {
        $.ajax({
            url: 'paginas/' + pag + '/listar-arquivos.php',
            method: 'POST',
            data: {
                id: id
            },
            dataType: 'html',
            success: function(resposta) {
                $('#lista-arquivos').html(resposta);
            },
            error: function() {
                $('#lista-arquivos').html('<p class="text-danger text-center">Erro ao carregar arquivos</p>');
            }
        });
    }

    $('#form-arquivo').on('submit', function(e) {
        e.preventDefault();
        var id = $('#id-conta-arquivos').val();
        var formData = new FormData(this);
        formData.append('id_conta', id);
        $.ajax({
            url: 'paginas/' + pag + '/arquivos.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'html',
            success: function(resposta) {
                if (resposta.indexOf('Sucesso') !== -1) {
                    $('#mensagem-arquivo').html('<span class="text-success">' + resposta + '</span>');
                    $('#arquivo-adicional').val('');
                    carregarListaArquivos(id);
                } else {
                    $('#mensagem-arquivo').html('<span class="text-danger">' + resposta + '</span>');
                }
            },
            error: function() {
                $('#mensagem-arquivo').html('<span class="text-danger">Erro na requisição</span>');
            }
        });
    });

    function excluirArquivo(id_arquivo, id_conta) {
        if (!confirm('Confirmar exclusão deste arquivo?')) return;
        $.ajax({
            url: 'paginas/' + pag + '/excluir-arquivo.php',
            method: 'POST',
            data: {
                id: id_arquivo,
                id_conta: id_conta
            },
            dataType: 'html',
            success: function(resposta) {
                if (resposta.indexOf('Sucesso') !== -1) {
                    carregarListaArquivos(id_conta);
                } else {
                    alert('Erro: ' + resposta);
                }
            },
            error: function() {
                alert('Erro na requisição');
            }
        });
    }
</script>