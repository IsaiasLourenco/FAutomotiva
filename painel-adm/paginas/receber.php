<?php
require_once('../conexao.php');
require_once('verificar.php');
$pag = 'receber';

// ✅ Busca configurações de multa/juros para exibir na label
$config_multa_juros = $pdo->query("SELECT multa_padrao, juros_padrao FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$multa_label = isset($config_multa_juros['multa_padrao'])
    ? number_format($config_multa_juros['multa_padrao'], 2, ',', '.')
    : '2,00';
$juros_label = isset($config_multa_juros['juros_padrao'])
    ? number_format($config_multa_juros['juros_padrao'], 2, ',', '.')
    : '0,33';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_sistema ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
        #tabela.tabela-pequena,
        #tabela.tabela-pequena th,
        #tabela.tabela-pequena td {
            font-size: 10px !important;
        }

        #tabela_wrapper {
            font-size: 10px !important;
            line-height: 1.4 !important;
        }

        #tabela_wrapper .dataTables_length,
        #tabela_wrapper .dataTables_filter {
            font-size: 10px !important;
            margin-bottom: 5px !important;
        }

        #tabela_wrapper .dataTables_length select,
        #tabela_wrapper .dataTables_filter input {
            font-size: 10px !important;
            padding: 2px 5px !important;
            height: 25px !important;
            margin: 0 5px !important;
            display: inline-block !important;
            width: auto !important;
            max-width: 80px !important;
        }

        #tabela_wrapper .dataTables_length label,
        #tabela_wrapper .dataTables_filter label {
            font-size: 10px !important;
            margin: 0 !important;
            font-weight: normal !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        #tabela_wrapper .dataTables_info {
            font-size: 10px !important;
            padding-top: 5px !important;
            line-height: 1.4 !important;
        }

        #tabela_wrapper .dataTables_paginate {
            font-size: 10px !important;
            padding-top: 5px !important;
        }

        #tabela_wrapper .dataTables_paginate .paginate_button {
            font-size: 10px !important;
            padding: 3px 8px !important;
            margin: 0 2px !important;
            min-width: 25px !important;
            height: 25px !important;
            line-height: 1.2 !important;
            border-radius: 2px !important;
        }

        #tabela_wrapper .dataTables_paginate .paginate_button.current,
        #tabela_wrapper .dataTables_paginate .paginate_button:hover {
            font-size: 10px !important;
        }

        #tabela_wrapper .row {
            margin: 0 !important;
        }

        #tabela_wrapper .col-sm-6,
        #tabela_wrapper .col-sm-12 {
            padding: 0 !important;
            width: 100% !important;
            float: none !important;
            text-align: center !important;
        }

        @media (max-width: 768px) {

            #tabela_wrapper .dataTables_length,
            #tabela_wrapper .dataTables_filter,
            #tabela_wrapper .dataTables_info,
            #tabela_wrapper .dataTables_paginate {
                float: none !important;
                text-align: center !important;
                margin: 5px 0 !important;
            }
        }
    </style>
</head>

<body>

    <div class="row mb-3 align-items-center d-flex flex-wrap">

        <?php
        // ✅ Verifica se veio da página de pacientes para mostrar link de voltar
        $voltar_para = @$_GET['voltar'] ?? '';
        $mostrar_voltar = ($voltar_para === 'pacientes');
        ?>

        <?php if ($mostrar_voltar): ?>
            <div class="alert alert-info py-2 mb-3 d-flex align-items-center justify-content-between">
                <div>
                    <i class="fa fa-info-circle mr-2"></i>
                    <strong>Vindo de:</strong> Pacientes → Baixa de Conta
                </div>
                <a href="index.php?pagina=pacientes" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-arrow-left mr-1"></i> Voltar para Pacientes
                </a>
            </div>
        <?php endif; ?>

        <!-- BOTÕES DA ESQUERDA -->
        <div class="col-md-4 col-sm-12 d-flex align-items-center flex-wrap gap-2 mb-2">

            <a onclick="inserir()" href="#" class="btn btn-primary btn-sm">
                <span class="fa fa-plus"></span> Conta
            </a>

            <li class="dropdown head-dpdn2" id="btn-deletar" style="display:inline-block;">
                <a href="#" class="btn btn-danger dropdown-toggle btn-sm" data-toggle="dropdown">
                    <span class="fa-solid fa-trash-can"></span> Excluir Conta
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <div class="notification_desc2">
                            <p class="mb-1">Confirmar Exclusão?
                                <a href="#" onclick="deletarSel()" class="btn btn-danger btn-xs">
                                    <span class="fa fa-check"></span> Sim, Excluir
                                </a>
                            </p>
                        </div>
                    </li>
                </ul>
            </li>

            <li class="dropdown head-dpdn2" id="btn-baixar" style="display:inline-block;">
                <a href="#" class="btn btn-success dropdown-toggle btn-sm" data-toggle="dropdown">
                    <span class="fa-solid fa-check-square"></span> Baixar Conta
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <div class="notification_desc2">
                            <p class="mb-1">Confirmar Baixa?
                                <a href="#" onclick="baixarSel()" class="btn btn-success btn-xs">
                                    <span class="fa fa-check"></span> Sim, Baixar
                                </a>
                            </p>
                            <p><strong>Total:</strong> <span id="totalContas"></span></p>
                        </div>
                    </li>
                </ul>
            </li>

        </div>

        <!-- FORM + FILTROS + BOTÃO PDF -->
        <form action="rel/rel_receber_class.php" method="post" target="_blank"
            class="col-md-8 col-sm-12 d-flex align-items-end flex-wrap gap-2">

            <div class="col-md-3 col-sm-6 mb-2">
                <label class="small text-muted mb-1">De:</label>
                <input type="date" name="dataInicial" id="dataInicial"
                    class="form-control form-control-sm" onchange="buscarData()">
            </div>

            <div class="col-md-3 col-sm-6 mb-2">
                <label class="small text-muted mb-1">Até:</label>
                <input type="date" name="dataFinal" id="dataFinal"
                    class="form-control form-control-sm" onchange="buscarData()">
            </div>

            <div class="col-md-3 col-sm-6 mb-2">
                <label class="small text-muted mb-1">Status:</label>
                <select name="pago" id="pago" class="form-control form-control-sm">
                    <option value="">Todas</option>
                    <option value="pagas">Pagas</option>
                    <option value="pendentes">Pendentes</option>
                    <option value="vencidas">Vencidas</option>
                </select>
            </div>

            <div class="col-md-2 col-sm-12 mb-2">
                <label class="small text-muted mb-1">&nbsp;</label>
                <span class="d-inline-flex align-items-center gap-1 text-nowrap filtro-rapido">
                    <a href="#" onclick="trocarData('mes')" class="text-decoration-none small">Mês</a>
                    <span class="text-muted">|</span>
                    <a href="#" onclick="trocarData('hoje')" class="text-decoration-none small">Hoje</a>
                    <span class="text-muted">|</span>
                    <a href="#" onclick="trocarData('ontem')" class="text-decoration-none small">Ontem</a>
                    <span class="text-muted">|</span>
                    <a href="#" onclick="trocarData('amanha')" class="text-decoration-none small">Amanhã</a>
                </span>
            </div>
            <input type="hidden" name="tipo_data" id="tipo_data_rel" value="vencimento">
            <!-- BOTÃO PDF SEMPRE NO CANTO DIREITO -->
            <div class="ms-auto mb-2">
                <button type="submit" class="btn-light" title="Relatório PDF">
                    <i class="fa-solid fa-file-pdf text-danger ico-grande"></i>
                </button>
            </div>

        </form>

    </div>

    <div class="bs-example widget-shadow table-primary" id="listar"></div>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        var pag = "<?php echo $pag; ?>"
    </script>
    <input type="hidden" id="ids">
</body>

</html>

<!-- Modal Inserir/Editar -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <span id="titulo_inserir"></span>
                </h4>
                <button id="btn-fechar" type="button" class="close mg-t--20" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="form" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <label for="descricao">Descrição</label>
                            <input type="text" class="form-control" id="descricao-perfil" name="descricao" required>
                        </div>

                        <div class="col-md-4">
                            <label for="paciente">Paciente</label>
                            <select name="paciente" id="paciente-perfil" class="form-control">
                                <option value="" selected disabled>Escolha um paciente...</option>
                                <?php $query = $pdo->query("SELECT * FROM pacientes ORDER BY nome asc");
                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                $total_reg = @count($res);
                                if ($total_reg > 0) {
                                    for ($i = 0; $i < $total_reg; $i++) {
                                        echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="0" disabled>Cadastre um Paciente</option>';
                                } ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="valor">Valor</label>
                            <input type="text" class="form-control moeda" id="valor-conta" name="valor" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="vencimento">Vencimento</label>
                            <input type="date" class="form-control" id="vencimento-conta" name="vencimento" value="<?php echo $data_atual ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="pago">Pago em</label>
                            <input type="date" class="form-control" id="pagamento-conta" name="pagamento">
                        </div>
                        <div class="col-md-3">
                            <label for="forma_pagamento">Forma de Pagamento</label>
                            <select class="form-control" name="forma_pagamento" id="forma_pagamento" required>
                                <option value="" selected disabled>Escolha uma forma de pagamento...</option>
                                <?php $query = $pdo->query("SELECT * FROM forma_pagamento ORDER BY nome asc");
                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                $total_reg = @count($res);
                                if ($total_reg > 0) {
                                    for ($i = 0; $i < $total_reg; $i++) {
                                        echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="0">Cadastre uma Forma de Pagamento</option>';
                                } ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="frequencia">Frequência</label>
                            <select name="frequencia" id="frequencia" class="form-control" required>
                                <option value="" selected disabled>Escolha uma frequência...</option>
                                <?php $query = $pdo->query("SELECT * FROM frequencias ORDER BY frequencia asc");
                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                $total_reg = @count($res);
                                if ($total_reg > 0) {
                                    for ($i = 0; $i < $total_reg; $i++) {
                                        echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['frequencia'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="0">Cadastre uma Frequência</option>';
                                } ?>
                            </select>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label for="obs">Observações</label>
                            <input type="text" class="form-control" id="obs-perfil" name="obs" required>
                        </div>
                        <div class="col-md-5">
                            <label for="arquivo">Arquivo</label>
                            <input type="file" class="form-control" id="arquivo-conta" name="arquivo" onchange="carregarImgReceber()">
                        </div>
                        <div class="col-md-2">
                            <img src="./images/receber/sem-foto.png" alt="Foto do arquivo" style="width: 80px;" id="target-arquivo">
                        </div>
                        <input type="hidden" name="id" id="id">
                    </div>
                    <div class="row mt-3 p-3 bg-light rounded">
                        <div class="col-12">
                            <strong>Ajustes Financeiros (Opcional)</strong>
                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Multa</label>
                            <input type="text" class="form-control moeda" name="multa" id="multa-perfil" placeholder="Auto">
                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Juros</label>
                            <input type="text" class="form-control moeda" name="juros" id="juros-perfil" placeholder="Auto">
                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Desconto</label>
                            <input type="text" class="form-control moeda" name="desconto" id="desconto-perfil" placeholder="Auto">
                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Taxa</label>
                            <input type="text" class="form-control moeda" name="taxa" id="taxa-perfil" placeholder="R$ 2,50">
                        </div>
                    </div>
                    <div id="mensagem" class="centro-pequeno"></div>
                </div>
                <div class="modal-footer centro">
                    <button type="submit" class="btn btn-primary" id="btn_salvar">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Fim Modal Inserir/Editar -->

<!-- Modal Dados -->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">
                    <span id="titulo_dados"></span>
                </h4>
                <button id="btn-fechar-dados" type="button" class="close text-white mg-t--20" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row br-btt pb-2">
                    <div class="col-md-6"><span><b>Descrição: </b></span><span id="descricao_dados-cli"></span></div>
                    <div class="col-md-6"><span><b>Paciente: </b></span><span id="paciente_dados-cli"></span></div>
                </div>
                <div class="row br-btt pb-2">
                    <div class="col-md-4">
                        <span><b>Valor: </b></span>
                        <span id="valor_dados-cli" class="text-success font-weight-bold"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Vencimento: </b></span><span id="vencimento_dados-cli"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Pago em: </b></span><span id="pagamento_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt pb-2">
                    <div class="col-md-6"><span><b>Forma de Pagamento: </b></span><span id="forma_pagamento_dados-cli"></span></div>
                    <div class="col-md-6"><span><b>Frequência: </b></span><span id="frequencia_dados-cli"></span></div>
                </div>
                <div class="row br-btt pb-2">
                    <div class="col-md-4"><span><b>Lançado em: </b></span><span id="lancamento_dados-cli"></span></div>
                    <div class="col-md-8"><span><b>Observações: </b></span><span id="obs_dados-cli"></span></div>
                </div>
                <div class="row br-btt pb-2">
                    <div class="col-12">
                        <span><b>Arquivo/Comprovante: </b></span><br>
                        <a id="link-arquivo-dados" href="#" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                            <i class="fa fa-eye"></i> Visualizar Arquivo
                        </a>
                        <img id="target-arquivo-dados"
                            src="./images/receber/sem-foto.png"
                            alt="Comprovante"
                            class="mt-2"
                            style="max-width: 200px; border-radius: 4px;">
                    </div>
                </div>
                <div class="row bg-light p-2 rounded">
                    <div class="col-md-3">
                        <small class="text-muted">Multa</small><br>
                        <span id="multa_dados-cli" class="font-weight-bold"></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Juros</small><br>
                        <span id="juros_dados-cli" class="font-weight-bold"></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Desconto</small><br>
                        <span id="desconto_dados-cli" class="font-weight-bold"></span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Taxa</small><br>
                        <span id="taxa_dados-cli" class="font-weight-bold"></span>
                    </div>
                </div>
                <div class="row mt-2 bg-success text-white p-2 rounded text-center">
                    <div class="col-12"><b>Subtotal:</b> <span id="subtotal_dados-cli" class="font-weight-bold"></span></div>
                </div>
                <div class="row bg-light p-2 rounded mt-2">
                    <div class="col-md-6">
                        <small class="text-muted">Lançado por:</small><br>
                        <span id="usuario_lanc_dados-cli" class="font-weight-bold"></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Baixa por:</small><br>
                        <span id="usuario_pgto_dados-cli" class="font-weight-bold"></span>
                    </div>
                </div>
                <div class="row mt-2" id="row-referencia" style="display:none;">
                    <div class="col-12">
                        <span><b>Referência: </b></span>
                        <span id="referencia_dados-cli" style="font-weight: 600; color: #2c3e50;"></span>
                        <small class="text-muted" style="display: inline-block; margin-left: 8px;">
                            (ID: <span id="id-referencia_dados-cli"
                                style="font-weight: 700; color: #1b6e74; background: #e8f4f8; padding: 2px 6px; border-radius: 3px;">
                            </span>)
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer centro">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<!-- Fim Modal Dados -->

<!-- ✅ Modal Parcelar -->
<div class="modal fade" id="modalParcelar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title"><i class="fa-solid fa-calendar-check"></i> Parcelar Conta: <span id="nome-parcelar"></span></h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="margin-top:-20px;"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="form-parcelar" autocomplete="off">
                <div class="modal-body">
                    <div class="row mb-3 pb-2 border-bottom">
                        <div class="col-md-3"><label class="small text-muted">Valor Total</label><input type="text" class="form-control form-control-sm moeda" id="valor-parcelar" name="valor-parcelar" readonly></div>
                        <div class="col-md-2"><label class="small text-muted">Parcelas</label><input type="number" class="form-control form-control-sm" id="qtd-parcelar" name="qtd-parcelar" min="2" max="24" value="2" required></div>
                        <div class="col-md-4">
                            <label class="small text-muted">Frequência</label>
                            <select class="form-control form-control-sm" id="frequencia-parcelar" name="frequencia" required>
                                <option value="">Selecione...</option>
                                <?php $query = $pdo->query("SELECT * FROM frequencias ORDER BY id ASC");
                                foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $freq) {
                                    if (!in_array($freq['frequencia'], ['Uma Vez', 'Única', 'Nenhuma'])) {
                                        $selected = ($freq['frequencia'] == 'Mensal') ? 'selected' : '';
                                        echo "<option value='{$freq['id']}' data-dias='{$freq['dias']}' $selected>{$freq['frequencia']}</option>";
                                    }
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted">Forma de Pagamento</label>
                            <select class="form-control form-control-sm" id="forma-pagamento-parcelas" name="forma_pagamento" required>
                                <option value="">Selecione...</option>
                                <?php $query = $pdo->query("SELECT * FROM forma_pagamento ORDER BY nome ASC");
                                foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $fp) {
                                    $taxa = 0;
                                    $nome = strtolower($fp['nome']);
                                    if (strpos($nome, 'débito') !== false || strpos($nome, 'debito') !== false) {
                                        $taxa = 3;
                                    } elseif (strpos($nome, 'crédito') !== false || strpos($nome, 'credito') !== false) {
                                        $taxa = 5;
                                    }
                                    echo "<option value='{$fp['id']}' data-taxa='{$taxa}'>{$fp['nome']}</option>";
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 p-2 bg-light rounded">
                        <div class="col-12 mb-1"><small class="text-muted"><b>Ajustes Financeiros (Opcional)</b></small></div>
                        <div class="col-md-3"><label class="small text-muted">Multa</label><input type="text" class="form-control form-control-sm moeda" id="multa-parcelar" name="multa" placeholder="R$ 0,00"></div>
                        <div class="col-md-3"><label class="small text-muted">Juros</label><input type="text" class="form-control form-control-sm moeda" id="juros-parcelar" name="juros" placeholder="R$ 0,00"></div>
                        <div class="col-md-3"><label class="small text-muted">Desconto</label><input type="text" class="form-control form-control-sm moeda" id="desconto-parcelar" name="desconto" placeholder="R$ 0,00"></div>
                        <div class="col-md-3"><label class="small text-muted">Taxa (%)</label><input type="number" class="form-control form-control-sm" id="taxa-parcelar" name="taxa" min="0" max="100" step="0.01" placeholder="0"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><label class="small text-muted">Vencimento da 1ª Parcela</label><input type="date" class="form-control form-control-sm" id="data-primeira-parcela" name="data-primeira" required></div>
                        <div class="col-md-6"><label class="small text-muted">&nbsp;</label>
                            <div class="alert alert-info py-2 mb-0"><small><i class="fa fa-calculator"></i> <strong>Valor por parcela:</strong> <span id="valor-por-parcela" class="font-weight-bold text-primary">R$ 0,00</span><span id="info-resto" class="text-muted small"></span></small></div>
                        </div>
                    </div>
                    <label class="small text-muted mb-2">Preview das Parcelas:</label>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered tabela-pequena" id="tabela-parcelas" style="display:none;">
                            <thead class="bg-light">
                                <tr>
                                    <th width="10%">Parcela</th>
                                    <th width="30%">Vencimento</th>
                                    <th width="30%">Valor</th>
                                    <th width="30%">Detalhes</th>
                                </tr>
                            </thead>
                            <tbody id="lista-parcelas"></tbody>
                        </table>
                    </div>
                    <input type="hidden" name="id-parcelar" id="id-parcelar">
                    <input type="hidden" name="descricao-original" id="descricao-original">
                    <input type="hidden" name="freq_id" id="freq-id-hidden">
                    <div id="mensagem-parcelar" class="mt-2"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" id="btn-cancelar-parcelar"><i class="fa fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-confirmar-parcelar"><i class="fa fa-check"></i> Confirmar Parcelamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- ✅ Fim Modal Parcelar -->

<!-- ✅ Modal Baixar -->
<div class="modal fade" id="modalBaixar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa fa-check"></i> Baixar Conta</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-baixar">
                <div class="modal-body">
                    <p><b>Descrição:</b> <span id="descricao-baixar"></span></p>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Valor</label>
                            <input type="text" class="form-control moeda" id="valor-baixar" name="valor" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Forma de Pagamento</label>
                            <select class="form-control" name="forma_pagamento" id="saida-baixar" required>
                                <option value="">Selecione...</option>
                                <?php $query = $pdo->query("SELECT * FROM forma_pagamento ORDER BY nome ASC");
                                foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $fp) {
                                    $taxa = 0;
                                    $nome = strtolower($fp['nome']);
                                    if (strpos($nome, 'débito') !== false || strpos($nome, 'debito') !== false) {
                                        $taxa = 3;
                                    } elseif (strpos($nome, 'crédito') !== false || strpos($nome, 'credito') !== false) {
                                        $taxa = 5;
                                    }
                                    echo "<option value='{$fp['id']}' data-taxa='{$taxa}'>{$fp['nome']}</option>";
                                } ?>
                            </select>
                        </div>
                    </div>

                    <!-- ✅ ADICIONE após o campo "Valor" -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pagamento-parcial" name="pagamento_parcial">
                                <label class="form-check-label" for="pagamento-parcial">Pagamento Parcial (Resíduo)</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="div-valor-parcial" style="display:none;">
                            <label>Valor Recebido</label>
                            <input type="text" class="form-control moeda" id="valor-parcial" name="valor_parcial" placeholder="R$ 0,00">
                        </div>
                        <div class="col-md-6" id="div-saldo-restante" style="display:none;">
                            <label>Saldo Restante</label>
                            <input type="text" class="form-control" id="saldo-restante" readonly>
                        </div>
                    </div>

                    <div class="row mt-3 bg-light p-2 rounded">
                        <div class="col-12"><b>Ajustes Financeiros</b></div>
                        <div class="col-md-3"><label>Multa</label><input type="text" class="form-control moeda" name="multa" id="valor-multa"></div>
                        <div class="col-md-3"><label>Juros</label><input type="text" class="form-control moeda" name="juros" id="valor-juros"></div>
                        <div class="col-md-3"><label>Desconto</label><input type="text" class="form-control moeda" name="desconto" id="valor-desconto"></div>
                        <div class="col-md-3"><label>Taxa (%)</label><input type="number" class="form-control" name="taxa" id="valor-taxa" step="0.01"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6"><label>Data do Pagamento</label><input type="date" class="form-control" name="data_pgto" value="<?php echo date('Y-m-d'); ?>"></div>
                        <div class="col-md-6"><label>Subtotal</label><input type="text" class="form-control" id="subtotal-baixar" name="subtotal" readonly></div>
                    </div>
                    <input type="hidden" name="id" id="id-baixar">
                    <input type="hidden" id="data-vencimento-baixar">
                    <div id="mensagem-baixar" class="mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btn-fechar-baixar" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btn-confirmar-baixar">Confirmar Baixa</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- ✅ Fim Modal Baixar -->

<!-- ✅ Modal de Relacionados (Parcelas/Resíduos) -->
<div class="modal fade" id="modalRelacionados" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">
                    <i class="fa-solid fa-diagram-project"></i>
                    Relacionados: <span id="titulo-relacionados"></span>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%" class="text-center">Tipo</th>
                                <th width="35%">Descrição</th>
                                <th width="15%" class="text-center">Pago em</th>
                                <th width="15%" class="text-center">Forma Pgto</th>
                                <th width="30%" class="text-end">Valores</th>
                            </tr>
                        </thead>
                        <tbody id="lista-relacionados">
                            <tr>
                                <td colspan="5" class="text-center text-muted">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ✅ Resumo no rodapé da modal -->
                <div class="row mt-3 pt-3 border-top" id="resumo-relacionados" style="display:none;">
                    <div class="col-md-4">
                        <small class="text-muted">Total Pago:</small>
                        <h5 class="text-success font-weight-bold" id="total-pago">R$ 0,00</h5>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Saldo Restante:</small>
                        <h5 class="text-danger font-weight-bold" id="saldo-restante">R$ 0,00</h5>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Valor Original:</small>
                        <h5 class="text-dark font-weight-bold" id="valor-original">R$ 0,00</h5>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<!-- ✅ Modal de Relacionados (Parcelas/Resíduos) -->

<!-- ✅ Modal de Baixa Múltipla -->
<div class="modal fade" id="modalBaixarMultiplo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title"><i class="fa fa-check-square"></i> Baixar Múltiplas Contas</h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p><strong>Contas selecionadas:</strong> <span id="qtd-contas-selecionadas">0</span></p>
                <p><strong>Total a receber:</strong> <span id="total-multiplo" class="text-success font-weight-bold">R$ 0,00</span></p>

                <div class="form-group">
                    <label>Forma de Pagamento (para todas)</label>
                    <select class="form-control" id="forma-pagamento-multiplo" required>
                        <option value="">Selecione...</option>
                        <?php $query = $pdo->query("SELECT * FROM forma_pagamento ORDER BY nome ASC");
                        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $fp) {
                            $taxa = 0;
                            $nome = strtolower($fp['nome']);
                            if (strpos($nome, 'débito') !== false || strpos($nome, 'debito') !== false) {
                                $taxa = 3;
                            } elseif (strpos($nome, 'crédito') !== false || strpos($nome, 'credito') !== false) {
                                $taxa = 5;
                            }
                            echo "<option value='{$fp['id']}' data-taxa='{$taxa}'>{$fp['nome']}</option>";
                        } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Data do Pagamento</label>
                    <input type="date" class="form-control" id="data-pagamento-multiplo" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="aplicar-multas-multiplo">
                    <label class="form-check-label" for="aplicar-multas-multiplo">
                        Aplicar multa (<?php echo $multa_label; ?>%) e juros (<?php echo $juros_label; ?>%/mês) se vencidas
                    </label>
                </div>

                <div id="mensagem-baixar-multiplo" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-baixar-multiplo">Confirmar Baixa</button>
            </div>
        </div>
    </div>
</div>
<!-- ✅ Fim Modal de Baixa Múltipla -->

<!-- ✅ Modal de Arquivos Adicionais -->
<div class="modal fade" id="modalArquivos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-paperclip"></i> 📎 Arquivos Anexados
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">

                <!-- ✅ Título da conta -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body py-2">
                        <h6 class="mb-0 text-primary"><i class="fa fa-folder-open"></i> Conta: <span id="titulo-arquivos" class="font-weight-bold"></span></h6>
                    </div>
                </div>

                <!-- ✅ Tabela de arquivos -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0"><i class="fa fa-list"></i> Arquivos vinculados</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="tabela-arquivos">
                                <thead class="bg-secondary text-white">
                                    <tr>
                                        <th width="5%" class="text-center">Tipo</th>
                                        <th width="45%">Arquivo</th>
                                        <th width="15%" class="text-center">Data</th>
                                        <th width="15%" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="lista-arquivos">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Carregando...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ✅ Upload de novo arquivo -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fa fa-upload"></i> Anexar novo arquivo</h6>
                    </div>
                    <div class="card-body">
                        <form id="form-arquivo" enctype="multipart/form-data">
                            <input type="hidden" id="id-conta-arquivos" name="id_conta">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label class="small text-muted mb-1">Selecione o arquivo</label>
                                    <input type="file" class="form-control form-control-sm" id="arquivo-adicional" name="arquivo" required>
                                    <small class="text-muted">PDF, JPG, PNG, XLS, DOC (máx. 5MB)</small>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success btn-sm btn-block">
                                        <i class="fa fa-upload"></i> Anexar
                                    </button>
                                </div>
                            </div>
                            <div id="mensagem-arquivo" class="mt-2 small"></div>
                        </form>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
            </div>
        </div>
    </div>
</div>
<!-- ✅ Fim Modal de Arquivos Adicionais -->

<script src="../js/ajax.js"></script>
<script>
    function formatarMoedaInput(input) {
        let valor = input.value.replace(/\D/g, "");
        valor = (valor / 100).toFixed(2) + "";
        valor = valor.replace(".", ",");
        valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        input.value = "R$ " + valor;
    }
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".moeda").forEach(input => {
            input.addEventListener("input", function() {
                formatarMoedaInput(this);
            });
            input.addEventListener("blur", function() {
                if (this.value.trim() === "" || this.value === "R$ ") {
                    this.value = "R$ 0,00";
                }
            });
        });
    });

    function buscarData() {
        var dataInicial = $('#dataInicial').val() || '';
        var dataFinal = $('#dataFinal').val() || '';
        var statusPago = $('#pago').val() || '';
        var tipoData = $('#tipoData').val() || 'vencimento';
        listar(dataInicial, dataFinal, statusPago, tipoData);
    }

    function trocarData(tipo) {
        var hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        var ontem = new Date(hoje);
        ontem.setDate(ontem.getDate() - 1);
        var amanha = new Date(hoje);
        amanha.setDate(amanha.getDate() + 1);

        function formatarDataISO(data) {
            var ano = data.getFullYear();
            var mes = String(data.getMonth() + 1).padStart(2, '0');
            var dia = String(data.getDate()).padStart(2, '0');
            return ano + '-' + mes + '-' + dia;
        }
        var dataHoje = formatarDataISO(hoje),
            dataOntem = formatarDataISO(ontem),
            dataAmanha = formatarDataISO(amanha);
        var inicioMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1),
            fimMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
        var dataInicioMes = formatarDataISO(inicioMes),
            dataFimMes = formatarDataISO(fimMes);
        if (tipo == 'mes') {
            $('#dataInicial').val(dataInicioMes);
            $('#dataFinal').val(dataFimMes);
            $('#tipoData').val('lancamento');
            $('#tipo_data_rel').val('lancamento');
        }
        if (tipo == 'hoje') {
            $('#dataInicial').val(dataHoje);
            $('#dataFinal').val(dataHoje);
            $('#tipoData').val('lancamento');
            $('#tipo_data_rel').val('lancamento');
        }
        if (tipo == 'ontem') {
            $('#dataInicial').val(dataOntem);
            $('#dataFinal').val(dataOntem);
            $('#tipoData').val('lancamento');
            $('#tipo_data_rel').val('lancamento');
        }
        if (tipo == 'amanha') {
            $('#dataInicial').val(dataAmanha);
            $('#dataFinal').val(dataAmanha);
            $('#tipoData').val('lancamento');
            $('#tipo_data_rel').val('lancamento');
        }
        buscarData();
    }

    function porData(tipo) {
        $('#tipoData').val(tipo);
        $('#tipo_data_rel').val(tipo);
        if (tipo == 'vencimento') {
            $('#pago').val('Não');
        }
        buscarData();
    }
    $('#pago').on('change', function() {
        buscarData();
    });
    $('#dataInicial, #dataFinal').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarData();
        }
    });

    $(document).ready(function() {

        // ✅ 1. Carrega a tabela inicial
        listar();

        // ✅ 2. Esconde botões de ação em lote
        $('#btn-deletar').hide();
        $('#btn-baixar').hide();

        // ✅ 3. Restaura checkboxes de baixa após tabela carregar
        setTimeout(restaurarCheckboxesBaixar, 500);

        // ✅ 4. NOVO: Verifica se veio parâmetro de baixa da página de pacientes
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('baixar_id')) {
            var id = urlParams.get('baixar_id');
            var valor = urlParams.get('baixar_valor');
            var descricao = urlParams.get('baixar_desc');

            // ✅ Aguarda a tabela carregar e chama a função global baixar()
            setTimeout(function() {
                if (typeof baixar === 'function') {
                    // Chama com 5 parâmetros (os dois últimos vazios, pois não temos aqui)
                    baixar(id, valor, descricao, '', '');
                }
            }, 800); // Um pouco mais de tempo para garantir que o modal está no DOM
        }

    });

    // ✅ Restaurar estado dos checkboxes após listar
    function restaurarCheckboxesBaixar() {
        idsBaixarSelecionados.forEach(function(item) {
            var cb = document.querySelector('.check-baixar[data-id="' + item.id + '"]');
            if (cb) {
                cb.checked = true;
            }
        });
    }

    function parcelar(id, valor, descricao, multa, juros, desconto) {
        limparModalParcelar();
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
    $('#form-parcelar').off('submit').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#btn-confirmar-parcelar');
        var originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processando...');
        $.ajax({
            url: 'paginas/receber/parcelar.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: "html",
            success: function(resposta) {
                if (resposta.indexOf('Sucesso:') === 0) {
                    $('#mensagem-parcelar').html('<div class="alert alert-success py-1 mb-0"><small>' + resposta + '</small></div>');
                    setTimeout(function() {
                        $('#modalParcelar').modal('hide');
                        limparModalParcelar();
                        listar();
                    }, 1500);
                } else {
                    $('#mensagem-parcelar').html('<div class="alert alert-danger py-1 mb-0"><small>' + resposta + '</small></div>');
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                $('#mensagem-parcelar').html('<div class="alert alert-danger py-1 mb-0"><small>Erro na requisição: ' + error + '</small></div>');
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
    $('#btn-cancelar-parcelar, #modalParcelar .close').on('click', function() {
        limparModalParcelar();
    });
    $('#modalParcelar').on('hidden.bs.modal', function() {
        limparModalParcelar();
    });

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

    function mostrar(descricao, paciente, valor, data_vencimento, data_lancamento, data_pagamento, forma_pagamento, frequencia, obs, arquivo, multa,
        juros, desconto, taxa, subtotal, usuario_lanc, usuario_pgto, referencia, id_referencia) {
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

        // ✅ Referência (mostra só se tiver valor)
        if (referencia && referencia !== 'null' && referencia !== '') {
            $('#referencia_dados-cli').text(referencia);
            $('#id-referencia_dados-cli').text(id_referencia || '-'); // se tiver id_referencia também
            $('#row-referencia').show();
        } else {
            $('#row-referencia').hide();
        }

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
            if (ids.indexOf(id + '-') === -1) {
                $('#ids').val(ids + id + '-');
            }
        } else {
            $('#ids').val(ids.replace(id + '-', ''));
        }

        // ✅ Checkbox da descrição controla APENAS botão de EXCLUIR
        var idsVal = $('#ids').val().trim();
        $('#btn-deletar').toggle(idsVal !== '');
    }

    function deletarSel() {
        var ids = $('#ids').val().split("-");
        for (var i = 0; i < ids.length - 1; i++) {
            if (ids[i]) excluir(ids[i]);
        }
        limparCampos();
    }

    function baixarSel() {
        // ✅ Pega IDs do array persistente (não do $('#ids'))
        var ids = idsBaixarSelecionados.map(item => item.id);

        if (ids.length === 0) {
            alert('Nenhuma conta selecionada!');
            return;
        }

        // ✅ Preenche dados na modal
        $('#qtd-contas-selecionadas').text(ids.length);

        var total = idsBaixarSelecionados.reduce((sum, item) => sum + item.valor, 0);
        $('#total-multiplo').text('R$ ' + total.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));

        $('#forma-pagamento-multiplo').val('');
        $('#data-pagamento-multiplo').val(new Date().toISOString().split('T')[0]);
        $('#aplicar-multas-multiplo').prop('checked', false);
        $('#mensagem-baixar-multiplo').html('');

        // ✅ Armazena IDs para uso no AJAX
        $('#modalBaixarMultiplo').data('ids', ids);

        // ✅ Abre a modal
        $('#modalBaixarMultiplo').modal('show');
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

    function baixar(id, valor, descricao, forma_pgto, data_vencimento) {
        $('#id-baixar').val(id);
        $('#descricao-baixar').text(descricao);
        $('#valor-baixar').val(valor);
        $('#data-vencimento-baixar').val(data_vencimento); // ✅ Campo oculto para cálculo de atraso
        if (forma_pgto && forma_pgto !== 'undefined') {
            $('#saida-baixar').val(forma_pgto).trigger('change');
        }

        $('#subtotal-baixar').val(valor);
        $('#valor-juros, #valor-desconto, #valor-multa, #valor-taxa').val('');
        $('#modalBaixar').modal('show');
        $('#mensagem-baixar').text('');
    }

    $("#form-baixar").submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var btn = $('#btn-confirmar-baixar');
        var originalText = btn.html();

        // ✅ Desabilita botão durante processamento
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processando...');

        $.ajax({
            url: 'paginas/' + pag + "/baixar.php",
            type: 'POST',
            data: formData,
            success: function(mensagem) {
                $('#mensagem-baixar').text('');
                $('#mensagem-baixar').removeClass();

                if (mensagem.indexOf('Sucesso') !== -1 || mensagem.indexOf('Resíduo registrado') !== -1) {
                    // ✅ Mostra mensagem de sucesso
                    $('#mensagem-baixar').addClass('text-success');
                    $('#mensagem-baixar').text(mensagem);

                    // ✅ Fecha modal após 1.5 segundos
                    setTimeout(function() {
                        $('#btn-fechar-baixar').click();
                        listar(); // ✅ Atualiza a lista automaticamente
                    }, 1500);
                } else {
                    // ✅ Mostra erro
                    $('#mensagem-baixar').addClass('text-danger');
                    $('#mensagem-baixar').text(mensagem);
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                $('#mensagem-baixar').addClass('text-danger');
                $('#mensagem-baixar').text('Erro na requisição: ' + error);
                btn.prop('disabled', false).html(originalText);
            },
            cache: false,
            contentType: false,
            processData: false,
        });
    });

    // ✅ AJAX para confirmar baixa múltipla
    // ✅ AJAX para confirmar baixa múltipla
    $('#btn-confirmar-baixar-multiplo').on('click', function() {
        // ✅ Pega IDs do array persistente
        var ids = idsBaixarSelecionados.map(item => item.id);

        var formaPagamento = $('#forma-pagamento-multiplo').val();
        var dataPagamento = $('#data-pagamento-multiplo').val();
        var aplicarMultas = $('#aplicar-multas-multiplo').is(':checked');

        if (!formaPagamento) {
            $('#mensagem-baixar-multiplo').html('<div class="alert alert-danger">Selecione uma forma de pagamento!</div>');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processando...');

        $.ajax({
            url: 'paginas/' + pag + "/baixar_multiplos.php",
            method: 'POST',
            data: {
                ids: ids,
                forma_pagamento: formaPagamento,
                data_pgto: dataPagamento,
                aplicar_multas: aplicarMultas ? 'sim' : 'nao'
            },
            dataType: 'html',
            success: function(mensagem) {
                if (mensagem.indexOf('Sucesso') !== -1) {
                    $('#mensagem-baixar-multiplo').html('<div class="alert alert-success">' + mensagem + '</div>');
                    setTimeout(function() {
                        $('#modalBaixarMultiplo').modal('hide');
                        idsBaixarSelecionados = []; // ✅ Limpa array
                        document.getElementById('btn-baixar').style.display = 'none';
                        document.getElementById('totalContas').innerText = '';
                        listar();
                    }, 1500);
                } else {
                    $('#mensagem-baixar-multiplo').html('<div class="alert alert-danger">Erro: ' + mensagem + '</div>');
                    btn.prop('disabled', false).html('Confirmar Baixa');
                }
            },
            error: function(xhr, status, error) {
                $('#mensagem-baixar-multiplo').html('<div class="alert alert-danger">Erro na requisição: ' + error + '</div>');
                btn.prop('disabled', false).html('Confirmar Baixa');
            }
        });
    });

    // Função para calcular subtotal
    function calcularSubtotalBaixa() {
        var valorStr = $('#valor-baixar').val();
        var valor = parseFloat(valorStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) || 0;

        var multaStr = $('#valor-multa').val();
        var multa = multaStr ? parseFloat(multaStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) : 0;

        var jurosStr = $('#valor-juros').val();
        var juros = jurosStr ? parseFloat(jurosStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) : 0;

        var descontoStr = $('#valor-desconto').val();
        var desconto = descontoStr ? parseFloat(descontoStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) : 0;

        var taxa = parseFloat($('#valor-taxa').val()) || 0;

        var subtotal = valor + multa + juros + (valor * taxa / 100) - desconto;

        $('#subtotal-baixar').val('R$ ' + subtotal.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    }

    // ✅ Auto-preencher taxa conforme forma de pagamento
    $('#saida-baixar').on('change', function() {
        var optionSelecionada = $(this).find('option:selected');
        var taxa = optionSelecionada.data('taxa') || 0;

        $('#valor-taxa').val(taxa > 0 ? taxa : '');

        // 🔥 FORÇA cálculo de multa e juros também
        $('[name="data_pgto"]').trigger('change');

        calcularSubtotalBaixa();
    });



    // ✅ Calcular multa/juros automáticos se data de pagamento > vencimento
    $('[name="data_pgto"]').on('change', function() {
        var dataPgto = $(this).val();
        var dataVencimentoStr = $('#data-vencimento-baixar').val(); // precisa ser passado na função baixar()

        if (dataPgto && dataVencimentoStr && dataPgto > dataVencimentoStr) {
            var diasAtraso = Math.ceil((new Date(dataPgto) - new Date(dataVencimentoStr)) / (1000 * 60 * 60 * 24));

            // Multa padrão: 2% do valor (se campo vazio)
            if (!$('#valor-multa').val() || $('#valor-multa').val() === 'R$ 0,00') {
                var valorStr = $('#valor-baixar').val();
                var valor = parseFloat(valorStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) || 0;
                var multa = valor * 0.02;
                $('#valor-multa').val('R$ ' + multa.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
            }

            // Juros padrão: 1% ao mês proporcional (se campo vazio)
            if (!$('#valor-juros').val() || $('#valor-juros').val() === 'R$ 0,00') {
                var valorStr = $('#valor-baixar').val();
                var valor = parseFloat(valorStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) || 0;
                var juros = valor * 0.01 * (diasAtraso / 30);
                $('#valor-juros').val('R$ ' + juros.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
            }
        }

        calcularSubtotalBaixa();
    });

    // ✅ Recalcular subtotal ao mudar qualquer campo de ajuste
    $('#valor-multa, #valor-juros, #valor-desconto, #valor-taxa').on('change input', function() {
        calcularSubtotalBaixa();
    });

    // ✅ Formatar campos de moeda na modal de baixa
    $('#valor-multa, #valor-juros, #valor-desconto').on('input', function() {
        let valor = this.value.replace(/\D/g, "");
        valor = (valor / 100).toFixed(2) + "";
        valor = valor.replace(".", ",");
        valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        this.value = "R$ " + valor;
        calcularSubtotalBaixa();
    });

    // ✅ Lógica de Pagamento Parcial (Resíduo) - CORRIGIDO
    $('#pagamento-parcial').on('change', function() {
        if ($(this).is(':checked')) {
            $('#div-valor-parcial, #div-saldo-restante').show();
            $('#valor-parcial').val($('#valor-baixar').val());
            $('#saldo-restante').val('R$ 0,00');
            calcularSaldoRestante(); // ✅ Calcula inicial
        } else {
            $('#div-valor-parcial, #div-saldo-restante').hide();
            $('#valor-parcial').val('');
            $('#saldo-restante').val('');
        }
    });

    // ✅ Função separada para calcular saldo
    function calcularSaldoRestante() {
        var valorTotalStr = $('#valor-baixar').val();
        var valorParcialStr = $('#valor-parcial').val();

        // ✅ Remove formatação de moeda corretamente
        var valorTotal = parseFloat(valorTotalStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) || 0;
        var valorParcial = parseFloat(valorParcialStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) || 0;

        var saldoRestante = valorTotal - valorParcial;

        $('#saldo-restante').val('R$ ' + saldoRestante.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    }

    // ✅ Usa blur em vez de input (evita cálculo durante digitação)
    $('#valor-parcial').on('blur', function() {
        if ($(this).val() !== '' && $(this).val() !== 'R$ ') {
            calcularSaldoRestante();
        }
    });

    // ✅ Também calcula ao mudar o valor (para quando o usuário clica fora)
    $('#valor-parcial').on('change', function() {
        calcularSaldoRestante();
    });

    // ✅ Muda texto do botão quando marca/desmarca "Pagamento Parcial"
    $('#pagamento-parcial').on('change', function() {
        if ($(this).is(':checked')) {
            $('#btn-confirmar-baixar').html('<i class="fa fa-check"></i> Receber Resíduo');
            $('#div-valor-parcial, #div-saldo-restante').show();
            $('#valor-parcial').val($('#valor-baixar').val());
            $('#saldo-restante').val('R$ 0,00');
            calcularSaldoRestante();
        } else {
            $('#btn-confirmar-baixar').html('<i class="fa fa-check"></i> Confirmar Baixa');
            $('#div-valor-parcial, #div-saldo-restante').hide();
            $('#valor-parcial').val('');
            $('#saldo-restante').val('');
        }
    });

    // ✅ Muda texto do botão quando marca/desmarca "Pagamento Parcial"
    $('#pagamento-parcial').on('change', function() {
        if ($(this).is(':checked')) {
            $('#btn-confirmar-baixar').html('<i class="fa fa-check"></i> Receber Resíduo');
        } else {
            $('#btn-confirmar-baixar').html('<i class="fa fa-check"></i> Confirmar Baixa');
        }
    });

    // ✅ Função para abrir modal de relacionados
    function mostrarRelacionados(id, descricao) {
        $('#titulo-relacionados').text(descricao);
        $('#lista-relacionados').html('<tr><td colspan="5" class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Carregando...</td></tr>');
        $('#resumo-relacionados').hide();
        $('#modalRelacionados').modal('show');

        $.ajax({
            url: 'paginas/receber/listar_relacionados.php',
            method: 'POST',
            data: {
                id: id
            },
            dataType: 'html',
            success: function(resposta) {
                $('#lista-relacionados').html(resposta);

                // ✅ Calcula resumo se houver dados
                if ($(resposta).find('tr').length > 1) {
                    calcularResumoRelacionados(id);
                }
            },
            error: function() {
                $('#lista-relacionados').html('<tr><td colspan="5" class="text-center text-danger">Erro ao carregar dados</td></tr>');
            }
        });
    }

    // ✅ Calcula e exibe resumo (total pago, saldo, etc.)
    function calcularResumoRelacionados(id_original) {
        $.ajax({
            url: 'paginas/receber/listar_relacionados.php',
            method: 'POST',
            data: {
                id: id_original,
                resumo: 'sim'
            },
            dataType: 'json',
            success: function(resposta) {
                if (resposta.sucesso) {
                    $('#total-pago').text(resposta.total_pago);
                    $('#saldo-restante').text(resposta.saldo_restante);
                    $('#valor-original').text(resposta.valor_original);
                    $('#resumo-relacionados').show();
                }
            }
        });
    }

    // ✅ Array persistente para armazenar IDs selecionados para baixa
    var idsBaixarSelecionados = [];

    // ✅ CONTROLE DE BAIXA MÚLTIPLA
    $(document).on('change', '.check-baixar', function() {
        var id = $(this).data('id');
        var valor = parseFloat($(this).data('valor')) || 0;

        // ✅ Adiciona ou remove do array persistente
        if ($(this).is(':checked')) {
            if (!idsBaixarSelecionados.includes(id)) {
                idsBaixarSelecionados.push({
                    id: id,
                    valor: valor
                });
            }
        } else {
            idsBaixarSelecionados = idsBaixarSelecionados.filter(item => item.id !== id);
        }

        // ✅ Calcula total do array (não do DOM)
        var total = idsBaixarSelecionados.reduce((sum, item) => sum + item.valor, 0);

        if (idsBaixarSelecionados.length > 0) {
            document.getElementById('btn-baixar').style.display = 'inline-block';
            document.getElementById('totalContas').innerText = 'R$ ' + total.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            document.getElementById('modalBaixarMultiplo').setAttribute('data-ids', JSON.stringify(idsBaixarSelecionados.map(i => i.id)));
        } else {
            document.getElementById('btn-baixar').style.display = 'none';
            document.getElementById('totalContas').innerText = '';
        }
    });

    // ✅ Abrir modal de arquivos
    function abrirArquivos(id, descricao) {
        $('#titulo-arquivos').text(descricao);
        $('#id-conta-arquivos').val(id);
        $('#lista-arquivos').html('<p class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Carregando...</p>');
        $('#mensagem-arquivo').text('');
        $('#arquivo-adicional').val('');

        // Carrega lista
        carregarListaArquivos(id);

        $('#modalArquivos').modal('show');
    }

    // ✅ Carregar lista de arquivos
    function carregarListaArquivos(id) {
        $.ajax({
            url: 'paginas/receber/listar-arquivos.php',
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

    // ✅ Upload de novo arquivo
    $('#form-arquivo').on('submit', function(e) {
        e.preventDefault();

        var id = $('#id-conta-arquivos').val();
        var formData = new FormData(this);
        formData.append('id_conta', id);

        $.ajax({
            url: 'paginas/receber/arquivos.php',
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

    // ✅ Excluir arquivo
    function excluirArquivo(id_arquivo, id_conta) {
        if (!confirm('Confirmar exclusão deste arquivo?')) return;

        $.ajax({
            url: 'paginas/receber/excluir-arquivo.php',
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