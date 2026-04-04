<?php
require_once('../conexao.php');
require_once('verificar.php');
$pag = 'pagar';

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

        <div class="col-md-4 col-sm-12 d-flex align-items-center flex-wrap gap-2 mb-2">

            <a onclick="inserir()" href="#" class="btn btn-primary mr-3 btn-sm">
                <span class="fa fa-plus"></span> Conta
            </a>
            <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
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
            <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-baixar">
                <a href="#" class="btn btn-success dropdown-toggle btn-sm" data-toggle="dropdown">
                    <span class="fa-solid fa-check-square"></span> Baixar Conta
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <div class="notification_desc2">
                            <p class="mb-1">Confirmar Baixa das contas selecionadas?
                                <a href="#" onclick="baixarSel()" class="btn btn-success btn-xs">
                                    <span class="fa fa-check"></span> Sim, Baixar
                                </a>
                            </p>
                            <p><strong>Total das Contas:</strong> <span id="totalContas"></span></p>
                        </div>
                    </li>
                </ul>
            </li>
        </div>
        <form action="rel/rel_receber_class.php" method="post" target="_blank"
            class="col-md-8 col-sm-12 d-flex align-items-end flex-wrap gap-2">

            <div class="col-md-3 col-sm-6 mb-2">
                <label class="small text-muted mb-1">De:</label>
                <input type="date" name="dataInicial" id="dataInicial" class="form-control form-control-sm" value="" onchange="buscarData()">
            </div>

            <div class="col-md-3 col-sm-6 mb-2">
                <label class="small text-muted mb-1">Até:</label>
                <input type="date" name="dataFinal" id="dataFinal" class="form-control form-control-sm" value="" onchange="buscarData()">
            </div>

            <div class="col-md-3 col-sm-6 mb-2">
                <label class="small text-muted mb-1">Status:</label>
                <select name="pago" id="pago" class="form-control form-control-sm">
                    <option value="">Todas</option>
                    <option value="Sim">Pagas</option>
                    <option value="Não">Pendentes</option>
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
            <div class="ms-auto mb-2">
                <button type="submit" class="btn btn-danger btn-sm" title="Relatório PDF">
                    <i class="fa-solid fa-file-pdf"></i>
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
                <h4 class="modal-title"><span id="titulo_inserir"></span></h4>
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
                            <label for="fornecedor">Fornecedor</label>
                            <select name="fornecedor" id="fornecedor-perfil" class="form-control">
                                <option value="" selected disabled>Escolha um fornecedor...</option>
                                <?php
                                $query = $pdo->query("SELECT * FROM fornecedores ORDER BY nome asc");
                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                $total_reg = @count($res);
                                if ($total_reg > 0) {
                                    for ($i = 0; $i < $total_reg; $i++) {
                                        echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="0" disabled>Cadastre um Fornecedor</option>';
                                }
                                ?>
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
                                <?php
                                $query = $pdo->query("SELECT * FROM forma_pagamento ORDER BY nome asc");
                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                $total_reg = @count($res);
                                if ($total_reg > 0) {
                                    for ($i = 0; $i < $total_reg; $i++) {
                                        echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="0">Cadastre uma Forma de Pagamento</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="frequencia">Frequência</label>
                            <select name="frequencia" id="frequencia" class="form-control" required>
                                <option value="" selected disabled>Escolha uma frequência...</option>
                                <?php
                                $query = $pdo->query("SELECT * FROM frequencias ORDER BY frequencia asc");
                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                $total_reg = @count($res);
                                if ($total_reg > 0) {
                                    for ($i = 0; $i < $total_reg; $i++) {
                                        echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['frequencia'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="0">Cadastre uma Frequência</option>';
                                }
                                ?>
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
                            <input type="file" class="form-control" id="arquivo-conta" name="arquivo" onchange="carregarImgPagar()">
                        </div>
                        <div class="col-md-2">
                            <img src="./images/pagar/sem-foto.png" alt="Foto do arquivo" style="width: 80px;" id="target-arquivo">
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
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Dados -->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title"><span id="titulo_dados"></span></h4>
                <button id="btn-fechar-dados" type="button" class="close text-white mg-t--20" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row br-btt pb-2">
                    <div class="col-md-6">
                        <span><b>Descrição: </b></span>
                        <span id="descricao_dados-cli"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Fornecedor: </b></span>
                        <span id="fornecedor_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt pb-2">
                    <div class="col-md-4">
                        <span><b>Valor: </b></span>
                        <span id="valor_dados-cli" class="text-success font-weight-bold"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Vencimento: </b></span>
                        <span id="vencimento_dados-cli"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Pago em: </b></span>
                        <span id="pagamento_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt pb-2">
                    <div class="col-md-6">
                        <span><b>Forma de Pagamento: </b></span>
                        <span id="forma_pagamento_dados-cli"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Frequência: </b></span>
                        <span id="frequencia_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt pb-2">
                    <div class="col-md-4">
                        <span><b>Lançado em: </b></span>
                        <span id="lancamento_dados-cli"></span>
                    </div>
                    <div class="col-md-8">
                        <span><b>Observações: </b></span>
                        <span id="obs_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt pb-2">
                    <div class="col-12">
                        <span><b>Arquivo/Comprovante: </b></span><br>
                        <a id="link-arquivo-dados" href="#" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                            <i class="fa fa-eye"></i> Visualizar Arquivo
                        </a>
                        <img id="target-arquivo-dados"
                            src="./images/pagar/sem-foto.png"
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
                    <div class="col-12">
                        <b>Subtotal:</b>
                        <span id="subtotal_dados-cli" class="font-weight-bold"></span>
                    </div>
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Modal Baixar (SIMPLIFICADO - SEM RESÍDUO) -->
<div class="modal fade" id="modalBaixar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fa fa-check"></i> Baixar Conta
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
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
                                <?php
                                $query = $pdo->query("SELECT * FROM forma_pagamento ORDER BY nome ASC");
                                foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $fp) {
                                    $taxa = 0;
                                    $nome = strtolower($fp['nome']);
                                    if (strpos($nome, 'débito') !== false || strpos($nome, 'debito') !== false) {
                                        $taxa = 3;
                                    } elseif (strpos($nome, 'crédito') !== false || strpos($nome, 'credito') !== false) {
                                        $taxa = 5;
                                    }
                                    echo "<option value='{$fp['id']}' data-taxa='{$taxa}'>{$fp['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3 bg-light p-2 rounded">
                        <div class="col-12">
                            <b>Ajustes Financeiros</b>
                        </div>
                        <div class="col-md-3">
                            <label>Multa</label>
                            <input type="text" class="form-control moeda" name="multa" id="valor-multa">
                        </div>
                        <div class="col-md-3">
                            <label>Juros</label>
                            <input type="text" class="form-control moeda" name="juros" id="valor-juros">
                        </div>
                        <div class="col-md-3">
                            <label>Desconto</label>
                            <input type="text" class="form-control moeda" name="desconto" id="valor-desconto">
                        </div>
                        <div class="col-md-3">
                            <label>Taxa (%)</label>
                            <input type="number" class="form-control" name="taxa" id="valor-taxa" step="0.01">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Data do Pagamento</label>
                            <input type="date" class="form-control" name="data_pgto" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Subtotal</label>
                            <input type="text" class="form-control" id="subtotal-baixar" name="subtotal" readonly>
                        </div>
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

<!-- ✅ Modal de Baixa Múltipla -->
<div class="modal fade" id="modalBaixarMultiplo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">
                    <i class="fa fa-check-square"></i> Baixar Múltiplas Contas
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Contas selecionadas:</strong> <span id="qtd-contas-selecionadas">0</span></p>
                <p><strong>Total a pagar:</strong> <span id="total-multiplo" class="text-success font-weight-bold">R$ 0,00</span></p>
                <div class="form-group">
                    <label>Forma de Pagamento (para todas)</label>
                    <select class="form-control" id="forma-pagamento-multiplo" required>
                        <option value="">Selecione...</option>
                        <?php
                        $query = $pdo->query("SELECT * FROM forma_pagamento ORDER BY nome ASC");
                        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $fp) {
                            $taxa = 0;
                            $nome = strtolower($fp['nome']);
                            if (strpos($nome, 'débito') !== false || strpos($nome, 'debito') !== false) {
                                $taxa = 3;
                            } elseif (strpos($nome, 'crédito') !== false || strpos($nome, 'credito') !== false) {
                                $taxa = 5;
                            }
                            echo "<option value='{$fp['id']}' data-taxa='{$taxa}'>{$fp['nome']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Data do Pagamento</label>
                    <input type="date" class="form-control" id="data-pagamento-multiplo" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <!-- <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="aplicar-multas-multiplo">
                    <label class="form-check-label" for="aplicar-multas-multiplo">
                        Aplicar multa (<?php echo $multa_label; ?>%) e juros (<?php echo $juros_label; ?>%/mês) se vencidas
                    </label>
                </div> -->
                <div id="mensagem-baixar-multiplo" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-baixar-multiplo">Confirmar Baixa</button>
            </div>
        </div>
    </div>
</div>

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
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body py-2">
                        <h6 class="mb-0 text-primary">
                            <i class="fa fa-folder-open"></i> Conta:
                            <span id="titulo-arquivos" class="font-weight-bold"></span>
                        </h6>
                    </div>
                </div>
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
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../js/ajax.js"></script>
<script>
    // ✅ Formatação de moeda
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

    // ✅ Filtros de data
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
            return data.getFullYear() + '-' +
                String(data.getMonth() + 1).padStart(2, '0') + '-' +
                String(data.getDate()).padStart(2, '0');
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
        }
        if (tipo == 'hoje') {
            $('#dataInicial').val(dataHoje);
            $('#dataFinal').val(dataHoje);
            $('#tipoData').val('lancamento');
        }
        if (tipo == 'ontem') {
            $('#dataInicial').val(dataOntem);
            $('#dataFinal').val(dataOntem);
            $('#tipoData').val('lancamento');
        }
        if (tipo == 'amanha') {
            $('#dataInicial').val(dataAmanha);
            $('#dataFinal').val(dataAmanha);
            $('#tipoData').val('lancamento');
        }
        buscarData();
    }

    function porData(tipo) {
        $('#tipoData').val(tipo);
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
        listar();
        $('#btn-deletar').hide();
        $('#btn-baixar').hide();
    });

    function restaurarCheckboxesBaixar() {
        idsBaixarSelecionados.forEach(function(item) {
            var cb = document.querySelector('.check-baixar[data-id="' + item.id + '"]');
            if (cb) {
                cb.checked = true;
            }
        });
    }

    $(document).ready(function() {
        listar();
        $('#btn-deletar').hide();
        setTimeout(restaurarCheckboxesBaixar, 500);
    });

    // ✅ Função editar
    function editar(id, descricao, fornecedor, valor, data_vencimento, data_lancamento, data_pagamento,
        forma_pagamento, frequencia, obs, arquivo, multa, juros, desconto, taxa, subtotal) {
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

        if (typeof $('#multa-perfil').val !== 'undefined')
            $('#multa-perfil').val(multa !== '-' ? multa : '');
        if (typeof $('#juros-perfil').val !== 'undefined')
            $('#juros-perfil').val(juros !== '-' ? juros : '');
        if (typeof $('#desconto-perfil').val !== 'undefined')
            $('#desconto-perfil').val(desconto !== '-' ? desconto.replace('- R$ ', '-') : '');
        if (typeof $('#taxa-perfil').val !== 'undefined')
            $('#taxa-perfil').val(taxa !== '-' ? taxa : '');

        if (arquivo && arquivo !== 'sem-foto.png') {
            $('#target-arquivo').attr("src", "./images/pagar/" + arquivo);
        } else {
            $('#target-arquivo').attr("src", "./images/pagar/sem-foto.png");
        }
        $('#modalForm').modal('show');
    }

    // ✅ Função mostrar
    function mostrar(descricao, fornecedor, valor, data_vencimento, data_lancamento, data_pagamento,
        forma_pagamento, frequencia, obs, arquivo, multa, juros, desconto, taxa, subtotal,
        usuario_lanc, usuario_pgto) {
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
        if (typeof $('#multa-perfil').val !== 'undefined')
            $('#multa-perfil, #juros-perfil, #desconto-perfil, #taxa-perfil').val('');
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

    // ✅ Função baixar (individual)
    function baixar(id, valor, descricao, forma_pgto, data_vencimento) {
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

    // ✅ AJAX para baixar conta individual
    $("#form-baixar").submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var btn = $('#btn-confirmar-baixar');
        var originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processando...');

        $.ajax({
            url: 'paginas/' + pag + "/baixar.php",
            type: 'POST',
            formData,
            success: function(mensagem) {
                $('#mensagem-baixar').text('');
                $('#mensagem-baixar').removeClass();
                if (mensagem.indexOf('Sucesso') !== -1) {
                    $('#mensagem-baixar').addClass('text-success');
                    $('#mensagem-baixar').text(mensagem);
                    setTimeout(function() {
                        $('#btn-fechar-baixar').click();
                        listar();
                    }, 1500);
                } else {
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
            processData: false
        });
    });

    // ✅ Função para abrir modal de baixa múltipla (CORREÇÃO CRÍTICA)
    function baixarSel() {
        var ids = idsBaixarSelecionados.map(item => item.id);

        if (ids.length === 0) {
            alert('Nenhuma conta selecionada!');
            return;
        }

        // Preenche dados no modal
        $('#qtd-contas-selecionadas').text(ids.length);

        var total = idsBaixarSelecionados.reduce((sum, item) => sum + item.valor, 0);
        $('#total-multiplo').text(
            'R$ ' + total.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')
        );

        // Reseta campos do modal
        $('#forma-pagamento-multiplo').val('');
        $('#data-pagamento-multiplo').val(new Date().toISOString().split('T')[0]);
        $('#aplicar-multas-multiplo').prop('checked', false);
        $('#mensagem-baixar-multiplo').html('');

        // Armazena IDs e abre modal
        $('#modalBaixarMultiplo').data('ids', ids);
        $('#modalBaixarMultiplo').modal('show');
    }

    // ✅ AJAX para baixar múltiplas
    $('#btn-confirmar-baixar-multiplo').on('click', function() {
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
                        idsBaixarSelecionados = [];
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

    // ✅ Calcular subtotal na baixa
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
        $('[name="data_pgto"]').trigger('change');
        calcularSubtotalBaixa();
    });

    // ✅ Calcular multa/juros automáticos se data de pagamento > vencimento
    $('[name="data_pgto"]').on('change', function() {
        var dataPgto = $(this).val();
        var dataVencimentoStr = $('#data-vencimento-baixar').val();
        if (dataPgto && dataVencimentoStr && dataPgto > dataVencimentoStr) {
            var diasAtraso = Math.ceil((new Date(dataPgto) - new Date(dataVencimentoStr)) / (1000 * 60 * 60 * 24));
            if (!$('#valor-multa').val() || $('#valor-multa').val() === 'R$ 0,00') {
                var valorStr = $('#valor-baixar').val();
                var valor = parseFloat(valorStr.replace('R$', '').replace(/\./g, '').replace(',', '.')) || 0;
                var multa = valor * 0.02;
                $('#valor-multa').val('R$ ' + multa.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
            }
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

    // ✅ Array para baixa múltipla
    var idsBaixarSelecionados = [];

    // ✅ Evento para checkboxes de baixa
    $(document).on('change', '.check-baixar', function() {
        var id = $(this).data('id');
        var valor = parseFloat($(this).data('valor')) || 0;
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

    // ✅ Funções de arquivos
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
            formData,
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