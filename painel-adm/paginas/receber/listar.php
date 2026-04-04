<?php
$tabela = 'receber';
require_once("../../../conexao.php");

function js_escape($str)
{
    if ($str === null) return '';
    return str_replace(["'", "\\", "\n", "\r", '"'], ["\\'", "\\\\", "\\n", "\\r", '\"'], (string)$str);
}

$dataInicial = @$_POST['p1'] ?? '';
$dataFinal   = @$_POST['p2'] ?? '';
$pago        = @$_POST['p3'] ?? '';  // Recebe: '', 'pagas', 'pendentes', 'vencidas'
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

// Filtro de período
if (!empty($dataInicial)) {
    $query .= " AND $colunaData >= :data_inicial";
    $params[':data_inicial'] = $dataInicial;
}
if (!empty($dataFinal)) {
    $query .= " AND $colunaData <= :data_final";
    $params[':data_final'] = $dataFinal;
}

// ✅ FILTRO DE STATUS (CORRIGIDO)
if ($pago === 'pagas') {
    // Pagas: data_pagamento preenchida
    $query .= " AND data_pagamento IS NOT NULL 
                AND data_pagamento != '' 
                AND data_pagamento != '0000-00-00'";
} elseif ($pago === 'pendentes') {
    // Pendentes: ainda não venceram E não foram pagas
    $query .= " AND data_vencimento >= :hoje
                AND (data_pagamento IS NULL OR data_pagamento = '' OR data_pagamento = '0000-00-00')";
    $params[':hoje'] = $hoje;
} elseif ($pago === 'vencidas') {
    // Vencidas: já venceram E não foram pagas
    $query .= " AND data_vencimento < :hoje
                AND (data_pagamento IS NULL OR data_pagamento = '' OR data_pagamento = '0000-00-00')";
    $params[':hoje'] = $hoje;
}
// Se $pago for vazio, não aplica filtro de status (mostra todas)

$query .= " ORDER BY id DESC";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);

// ✅ Inicializa totais
$total_pendentes = 0;
$total_pago = 0;

echo <<<HTML
<table class="table table-hover tabela-pequena" id="tabela">
    <thead> 
        <tr> 
            <th>Descrição</th>
            <th>Paciente</th>
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
        $paciente_id = $res[$i]['paciente'];
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

        // ✅ Acumula totais (AGORA COM VARIÁVEIS JÁ DEFINIDAS)
        if (!empty($data_pagamento) && $data_pagamento != '0000-00-00') {
            $total_pago += $valor;
        } else {
            $total_pendentes += $valor;
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
            $mostrarBotaoParcelar = false;
            $mostrarBotaoBaixar = false;
        } elseif (strpos($descricao, '(Parcelada)') !== false) {
            $classe_status = 'conta-parcelada';
            $mostrarBotaoParcelar = false;
            $mostrarBotaoBaixar = true;
        } elseif (!empty($data_vencimento) && $data_vencimento < date('Y-m-d')) {
            $classe_status = 'conta-vencida';
            $mostrarBotaoParcelar = true;
            $mostrarBotaoBaixar = true;
        } else {
            $classe_status = 'conta-nao-paga';
            $mostrarBotaoParcelar = true;
            $mostrarBotaoBaixar = true;
        }

        $qp = $pdo->prepare("SELECT nome FROM pacientes WHERE id = :id LIMIT 1");
        $qp->bindValue(":id", $paciente_id, PDO::PARAM_INT);
        $qp->execute();
        $paciente_nome = ($rp = $qp->fetch(PDO::FETCH_ASSOC)) ? $rp['nome'] : 'Paciente Desconhecido';

        $qf = $pdo->prepare("SELECT frequencia FROM frequencias WHERE id = :id LIMIT 1");
        $qf->bindValue(":id", $frequencia_id, PDO::PARAM_INT);
        $qf->execute();
        $frequencia_nome = ($rf = $qf->fetch(PDO::FETCH_ASSOC)) ? $rf['frequencia'] : 'Frequência Desconhecida';

        $qfp = $pdo->prepare("SELECT nome FROM forma_pagamento WHERE id = :id LIMIT 1");
        $qfp->bindValue(":id", $forma_pagamento_id, PDO::PARAM_INT);
        $qfp->execute();
        $forma_pagamento_nome = ($rfp = $qfp->fetch(PDO::FETCH_ASSOC)) ? $rfp['nome'] : 'Forma de Pagamento Desconhecida';

        $usuario_lanc_id = $res[$i]['usuario_lanc'] ?? null;
        $usuario_lanc_nome = 'Não informado';
        if ($usuario_lanc_id) {
            $qu = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id LIMIT 1");
            $qu->execute([":id" => $usuario_lanc_id]);
            $ru = $qu->fetch(PDO::FETCH_ASSOC);
            $usuario_lanc_nome = $ru['nome'] ?? 'Não encontrado';
        }

        $usuario_pgto_id = $res[$i]['usuario_pgto'] ?? null;
        $usuario_pgto_nome = 'Não pago';
        if ($usuario_pgto_id) {
            $qu2 = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id LIMIT 1");
            $qu2->execute([":id" => $usuario_pgto_id]);
            $ru2 = $qu2->fetch(PDO::FETCH_ASSOC);
            $usuario_pgto_nome = $ru2['nome'] ?? 'Não encontrado';
        }

        // ✅ Busca total de resíduos já pagos para esta conta (se for conta original)
        $total_residuos = 0;
        if (empty($res[$i]['referencia']) || $res[$i]['referencia'] != 'Resíduo') {
            $stmt_residuos = $pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) as total_residuos FROM receber WHERE id_referencia = :id AND referencia = 'Resíduo'");
            $stmt_residuos->execute([':id' => $id]);
            $total_residuos = $stmt_residuos->fetchColumn();
        }

        // ✅ Formata valores para exibição (FAZER ANTES DO HEREDOC)
        $total_residuosF = number_format($total_residuos, 2, ',', '.');
        $saldo_restante = $valor - $total_residuos;
        $saldo_restanteF = number_format($saldo_restante, 2, ',', '.');

        $e_descricao = js_escape($descricao);
        $e_paciente_nome = js_escape($paciente_nome);
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
                <td><input type="checkbox" id="seletor-{$id}" class="form-check-input" onchange="selecionar('{$id}')"> {$descricao}</td>
                <td>{$paciente_nome}</td>
                <td>{$data_lancamentoF}</td>
                <td>{$data_vencimentoF}</td>
                <td class="esc">{$data_pagamentoF_tabela}</td>
                <td>
                    <b>{$valorF}</b>
HTML;

        // ✅ Exibe "Recebido" APENAS se a conta estiver efetivamente paga
        if (!empty($data_pagamento) && $data_pagamento != '0000-00-00' && $subtotal > 0 && $subtotal != $valor) {
            echo <<<HTML
                    <br>
                    <small class="text-success font-weight-bold">
                    Recebido: {$subtotalF}
                    </small>
HTML;
        }

        // ✅ Exibe "Pago/Saldo" apenas se houver resíduos
        if ($total_residuos > 0) {
            echo <<<HTML
        <br>
        <small class="text-muted">
            Pago: R$ {$total_residuosF} | 
            Saldo: R$ {$saldo_restanteF}
        </small>
HTML;
        }

        echo <<<HTML
                </td>
                
                <td>
                    <a href="#" onclick="editar('{$id}','{$e_descricao}','{$paciente_id}','{$e_valorF}','{$e_data_vencimento_iso}','{$e_data_lancamentoF}','{$e_data_pagamento_iso}','{$forma_pagamento_id}','{$frequencia_id}','{$e_obs}','{$e_arquivo}','{$e_multaF}','{$e_jurosF}','{$e_descontoF}','{$e_taxaF}','{$e_subtotalF}')" title="Editar Dados">
                        <i class="fa fa-edit text-primary ico-grande"></i>
                    </a>
                    <li class="dropdown head-dpdn2" style="display: inline-block;">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Excluir Registro">
                            <i class="fa-solid fa-trash-can text-danger ico-grande"></i>
                        </a>
                        <ul class="dropdown-menu" style="margin-left:-230px;">
                            <li>
                                <div class="notification_desc2">
                                    <p>Confirmar Exclusão? <br>
                                        <a href="#" onclick="excluir('{$id}')" class="btn btn-danger btn-xs">
                                            <span class="alinhaDireita">Sim</span>
                                        </a>
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <a href="#" onclick="mostrar('{$e_descricao}',
                                                 '{$e_paciente_nome}',
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
        $stmt_relacionados = $pdo->prepare("SELECT COUNT(*) as total FROM receber WHERE id_referencia = :id AND referencia IN ('Parcela', 'Resíduo')");
        $stmt_relacionados->execute([':id' => $id]);
        $tem_relacionados = $stmt_relacionados->fetchColumn() > 0;

        if ($tem_relacionados) {
            echo <<<HTML
                    <a href="#" onclick="mostrarRelacionados('{$id}', 
                                                             '{$e_descricao}')" title="Ver Parcelas e Resíduos">
                        <i class="fa-solid fa-diagram-project text-dark ico-grande"></i>
                    </a>
HTML;
        }
        if ($mostrarBotaoParcelar) {
            echo <<<HTML
            <a href="#" onclick="parcelar('{$id}', 
                                          '{$e_valorF}', 
                                          '{$e_descricao}', 
                                          '{$e_multaF}', 
                                          '{$e_jurosF}', 
                                          '{$e_descontoF}')" title="Parcelar valor">
                <i class="fa-solid fa-calendar-days text-success ico-grande"></i>
            </a>
HTML;
        }

        if ($mostrarBotaoBaixar) {
            $valor_restante = $valor - $total_residuos;

            echo <<<HTML

            <input type="checkbox" class="check-baixar maozinha" data-id="{$id}" data-valor="{$valor_restante}" title="Selecionar para baixa">

HTML;
            $valor_restante = $valor - $total_residuos;
            $valor_restanteF = 'R$ ' . number_format($valor_restante, 2, ',', '.');
            $e_valor_restanteF = js_escape($valor_restanteF);
            echo <<<HTML

            <a href="#" onclick="baixar('{$id}', 
                                        '{$e_valor_restanteF}', 
                                        '{$e_descricao}', 
                                        '{$e_forma_pagamento_nome}', 
                                        '{$data_vencimento_iso}')" title="Baixar valor">
                <i class="fa-solid fa-square-check text-danger ico-grande"></i>
            </a>
    
HTML;
        }

        echo <<<HTML
            <!-- ✅ Botão para abrir modal de arquivos -->
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

echo <<<HTML
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6FF" class="text-end font-weight-bold">
                    <span class="text-danger">Total Pendentes: R$ {$total_pendentesF}</span> 
                    &nbsp;&nbsp;|&nbsp;&nbsp; 
                    <span class="text-success">Total Recebido: R$ {$total_pagoF}</span>
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

    .conta-parcelada {
        background-color: #ffcdd2 !important;
        border-left: 4px solid #d32f2f !important;
        font-weight: 600;
    }

    .conta-parcelada:hover {
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
    function editar(id, descricao, paciente, valor, data_vencimento, data_lancamento, data_pagamento, forma_pagamento, frequencia, obs, arquivo, multa,
        juros, desconto, taxa, subtotal) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Registro');
        $('#id').val(id);
        $('#descricao-perfil').val(descricao);
        $('#paciente-perfil').val(paciente);
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
            $('#target-arquivo').attr("src", "./images/receber/" + arquivo);
        } else {
            $('#target-arquivo').attr("src", "./images/receber/sem-foto.png");
        }
        $('#modalForm').modal('show');
    }

    function mostrar(descricao, paciente, valor, data_vencimento, data_lancamento, data_pagamento, forma_pagamento, frequencia, obs, arquivo, multa, juros,
        desconto, taxa, subtotal, usuario_lanc, usuario_pgto) {
        $('#titulo_dados').text('Detalhes: ' + descricao);
        $('#descricao_dados-cli').text(descricao);
        $('#paciente_dados-cli').text(paciente);
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
            $('#target-arquivo-dados').attr('src', './images/receber/' + arquivo).show();
            $('#link-arquivo-dados').attr('href', './images/receber/' + arquivo).show();
        } else {
            $('#target-arquivo-dados').attr('src', './images/receber/sem-foto.png');
            $('#link-arquivo-dados').hide();
        }
        $('#usuario_lanc_dados-cli').text(usuario_lanc);
        $('#usuario_pgto_dados-cli').text(usuario_pgto);
        $('#modalDados').modal('show');
    }

    function limparCampos() {
        $('#id, #descricao-perfil, #paciente-perfil, #valor-conta, #vencimento-conta, #pagamento-conta, #forma_pagamento, #frequencia, #obs-perfil').val('');
        if (typeof $('#multa-perfil').val !== 'undefined') $('#multa-perfil, #juros-perfil, #desconto-perfil, #taxa-perfil').val('');
        $('#arquivo-conta').val('');
        $('#target-arquivo').attr('src', './images/receber/sem-foto.png');
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

    function permissoes(id, nome) {
        $('#id_permissoes').val(id);
        $('#nome_permissoes').text(nome);
        $('#modalPermissoes').modal('show');
        listarPermissoes(id);
    }

    function listarPermissoes(id) {
        $.ajax({
            url: 'paginas/' + pag + "/listar_permissoes.php",
            method: 'POST',
            data: {
                id: id
            },
            dataType: "html",
            success: function(result) {
                $('#listar_permissoes').html(result);
                $('#mensagem_permissao').text('');
            }
        });
    }

    function adicionarPermissoes(id, acesso) {
        $.ajax({
            url: 'paginas/' + pag + "/add_permissoes.php",
            method: 'POST',
            data: {
                id: id,
                acesso: acesso
            },
            dataType: "text",
            success: function(result) {
                if (result.trim() === 'inserido' || result.trim() === 'removido') {
                    listarPermissoes(id);
                } else {
                    $('#mensagem_permissao').addClass('text-danger').text('Erro: ' + result);
                }
            }
        });
    }

    function marcarTodos() {
        var id_user = $('#id_permissoes').val();
        var marcado = $('#input_todos').is(':checked');
        $.ajax({
            url: 'paginas/' + pag + "/add_all_permissoes.php",
            method: 'POST',
            data: {
                id: id_user,
                acao: marcado ? 'marcar_todos' : 'desmarcar_todos'
            },
            dataType: "html",
            success: function(result) {
                listarPermissoes(id_user);
            }
        });
    }

    function parcelar(id, valor, descricao, multa, juros, desconto) {
        limparModalParcelar();
        setTimeout(function() {
            $('#id-parcelar').val(id);
            $('#descricao-original').val(descricao);
            $('#nome-parcelar').text(descricao);
            $('#valor-parcelar').val(valor);
            $('#multa-parcelar').val(multa !== '-' ? multa : '');
            $('#juros-parcelar').val(juros !== '-' ? juros : '');
            $('#desconto-parcelar').val(desconto !== '-' ? desconto : '');
            var hoje = new Date();
            $('#data-primeira-parcela').val(hoje.toISOString().split('T')[0]);
            $('#frequencia-parcelar')[0].selectedIndex = 0;
            $('#freq-id-hidden').val('');
            $('#tabela-parcelas').hide();
            $('#qtd-parcelar, #frequencia-parcelar, #data-primeira-parcela, #valor-parcelar, #forma-pagamento-parcelas, #multa-parcelar, #juros-parcelar, #desconto-parcelar, #taxa-parcelar').off('change input').on('change input', function() {
                if ($(this).is('#frequencia-parcelar')) {
                    $('#freq-id-hidden').val($('#frequencia-parcelar').val());
                }
                if ($('#valor-parcelar').val() && $('#frequencia-parcelar').val() && $('#data-primeira-parcela').val()) {
                    calcularParcelas();
                    $('#tabela-parcelas').show();
                } else {
                    $('#tabela-parcelas').hide();
                }
            });
            $('#modalParcelar').modal('show');
        }, 50);
    }

    function limparModalParcelar() {
        $('#mensagem-parcelar').html('');
        $('#lista-parcelas').html('');
        $('#qtd-parcelar').val('2');
        $('#multa-parcelar, #juros-parcelar, #desconto-parcelar, #taxa-parcelar').val('');
        $('#id-parcelar, #descricao-original, #freq-id-hidden').val('');
        $('#valor-por-parcela').text('R$ 0,00');
        $('#info-resto').text('');
        $('#frequencia-parcelar')[0].selectedIndex = 0;
        $('#forma-pagamento-parcelas')[0].selectedIndex = 0;
        $('#data-primeira-parcela').val('');
        $('#tabela-parcelas').hide();
        setTimeout(function() {
            $('#form-parcelar')[0].reset();
            $('#qtd-parcelar').val('2');
            $('#tabela-parcelas').hide();
        }, 10);
    }

    function calcularParcelas() {
        var valorTotalStr = $('#valor-parcelar').val();
        var valorTotal = parseFloat(valorTotalStr.replace('R$', '').replace(/\./g, '').replace(',', '.'));
        var qtdParcelas = parseInt($('#qtd-parcelar').val()) || 2;
        var dataPrimeira = $('#data-primeira-parcela').val();
        var nomeFrequencia = $('#frequencia-parcelar').find('option:selected').text().toLowerCase();
        var diasFrequencia = parseInt($('#frequencia-parcelar').find('option:selected').data('dias')) || 30;
        var optionSelecionada = $('#forma-pagamento-parcelas').find('option:selected');
        var taxaFormaPagamento = optionSelecionada.data('taxa');
        if (taxaFormaPagamento === undefined || taxaFormaPagamento === null) {
            taxaFormaPagamento = optionSelecionada.attr('data-taxa');
        }
        taxaFormaPagamento = (taxaFormaPagamento !== undefined && taxaFormaPagamento !== '') ? parseFloat(taxaFormaPagamento) : 0;
        var multaStr = $('#multa-parcelar').val();
        var multa = multaStr ? parseFloat(multaStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) : 0;
        var jurosStr = $('#juros-parcelar').val();
        var juros = jurosStr ? parseFloat(jurosStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) : 0;
        var descontoStr = $('#desconto-parcelar').val();
        var desconto = descontoStr ? parseFloat(descontoStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) : 0;
        var taxaManual = parseFloat($('#taxa-parcelar').val()) || 0;
        if (!valorTotal || !dataPrimeira || !nomeFrequencia) {
            $('#tabela-parcelas').hide();
            return;
        }
        var valorAjustado = valorTotal + multa + juros - desconto;
        if (taxaFormaPagamento > 0) {
            valorAjustado += valorAjustado * (taxaFormaPagamento / 100);
        }
        if (taxaManual > 0) {
            valorAjustado += valorAjustado * (taxaManual / 100);
        }
        var valorBase = Math.floor((valorAjustado / qtdParcelas) * 100) / 100;
        var resto = Math.round((valorAjustado - (valorBase * qtdParcelas)) * 100);
        $('#valor-por-parcela').text(formatarMoeda(valorBase));
        if (resto != 0) {
            $('#info-resto').text('(+ R$ ' + (resto / 100).toFixed(2).replace('.', ',') + ' na última)');
        } else {
            $('#info-resto').text('');
        }
        var html = '';
        var dataAtual = new Date(dataPrimeira + 'T00:00:00');
        for (var i = 1; i <= qtdParcelas; i++) {
            var valorParcela = valorBase;
            if (i == qtdParcelas && resto != 0) {
                valorParcela += resto / 100;
            }
            var dataFormatada = dataAtual.toLocaleDateString('pt-BR');
            var valorFormatado = formatarMoeda(valorParcela);
            var detalhes = [];
            if (multa > 0) detalhes.push('Multa: R$ ' + (multa / qtdParcelas).toFixed(2).replace('.', ','));
            if (juros > 0) detalhes.push('Juros: R$ ' + (juros / qtdParcelas).toFixed(2).replace('.', ','));
            if (desconto > 0) detalhes.push('Desc: -R$ ' + (desconto / qtdParcelas).toFixed(2).replace('.', ','));
            if (taxaFormaPagamento > 0) detalhes.push('Taxa: ' + taxaFormaPagamento + '%');
            if (taxaManual > 0) detalhes.push('Taxa manual: ' + taxaManual + '%');
            var detalhesHtml = detalhes.length > 0 ? '<small class="text-muted d-block">' + detalhes.join(' | ') + '</small>' : '';
            html += '<tr><td class="text-center"><strong>' + i + '/' + qtdParcelas + '</strong></td><td class="text-center">' + dataFormatada + '</td><td class="text-center text-primary font-weight-bold">' + valorFormatado + '</td><td class="text-center">' + detalhesHtml + '</td></tr>';
            dataAtual = avancarData(dataAtual, nomeFrequencia, diasFrequencia);
        }
        $('#lista-parcelas').html(html);
        $('#tabela-parcelas').show();
    }

    function avancarData(data, nomeFrequencia, dias) {
        var novaData = new Date(data);
        if (nomeFrequencia.indexOf('mensal') !== -1) {
            novaData.setMonth(novaData.getMonth() + 1);
        } else if (nomeFrequencia.indexOf('bimestral') !== -1) {
            novaData.setMonth(novaData.getMonth() + 2);
        } else if (nomeFrequencia.indexOf('trimestral') !== -1) {
            novaData.setMonth(novaData.getMonth() + 3);
        } else if (nomeFrequencia.indexOf('quinzenal') !== -1) {
            novaData.setDate(novaData.getDate() + 15);
        } else if (nomeFrequencia.indexOf('semanal') !== -1) {
            novaData.setDate(novaData.getDate() + 7);
        } else if (nomeFrequencia.indexOf('diário') !== -1 || nomeFrequencia.indexOf('diario') !== -1) {
            novaData.setDate(novaData.getDate() + 1);
        } else {
            novaData.setDate(novaData.getDate() + dias);
        }
        return novaData;
    }

    function formatarMoeda(valor) {
        return 'R$ ' + valor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    $('#forma-pagamento-parcelas').on('change', function() {
        var taxa = $(this).find('option:selected').data('taxa');
        $('#taxa-parcelar').val(taxa > 0 ? taxa : '');
        if ($('#valor-parcelar').val() && $('#frequencia-parcelar').val() && $('#data-primeira-parcela').val()) {
            calcularParcelas();
            $('#tabela-parcelas').show();
        }
    });
    $('#btn-cancelar-parcelar, #modalParcelar .close').on('click', function() {
        limparModalParcelar();
    });
    $('#modalParcelar').on('hidden.bs.modal', function() {
        limparModalParcelar();
    });

    function baixar(id, valor, descricao, forma_pgto) {
        $('#id-baixar').val(id);
        $('#descricao-baixar').text(descricao);
        $('#valor-baixar').val(valor);
        if (forma_pgto && forma_pgto !== 'undefined') {
            $('#saida-baixar').val(forma_pgto).change();
        }
        $('#subtotal-baixar').val(valor);
        $('#valor-juros, #valor-desconto, #valor-multa, #valor-taxa').val('');
        $('#modalBaixar').modal('show');
        $('#mensagem-baixar').text('');
    }
</script>