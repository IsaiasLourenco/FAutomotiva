<?php
require_once('../conexao.php');
require_once('verificar.php');
$pag = 'receber';
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
            font-size: 12px !important;
        }

        #tabela_wrapper {
            font-size: 12px !important;
            line-height: 1.4 !important;
        }

        #tabela_wrapper .dataTables_length,
        #tabela_wrapper .dataTables_filter {
            font-size: 12px !important;
            margin-bottom: 5px !important;
        }

        #tabela_wrapper .dataTables_length select,
        #tabela_wrapper .dataTables_filter input {
            font-size: 12px !important;
            padding: 2px 5px !important;
            height: 25px !important;
            margin: 0 5px !important;
            display: inline-block !important;
            width: auto !important;
            max-width: 80px !important;
        }

        #tabela_wrapper .dataTables_length label,
        #tabela_wrapper .dataTables_filter label {
            font-size: 12px !important;
            margin: 0 !important;
            font-weight: normal !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        #tabela_wrapper .dataTables_info {
            font-size: 12px !important;
            padding-top: 5px !important;
            line-height: 1.4 !important;
        }

        #tabela_wrapper .dataTables_paginate {
            font-size: 12px !important;
            padding-top: 5px !important;
        }

        #tabela_wrapper .dataTables_paginate .paginate_button {
            font-size: 12px !important;
            padding: 3px 8px !important;
            margin: 0 2px !important;
            min-width: 25px !important;
            height: 25px !important;
            line-height: 1.2 !important;
            border-radius: 2px !important;
        }

        #tabela_wrapper .dataTables_paginate .paginate_button.current,
        #tabela_wrapper .dataTables_paginate .paginate_button:hover {
            font-size: 12px !important;
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
    <div class="row mb-3 align-items-center">
        <div class="col-md-4 col-sm-12 mb-2 mb-md-0">
            <a onclick="inserir()" href="#" class="btn btn-primary mr-3 btn-sm"><span class="fa fa-plus"></span> Conta</a>
            <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
                <a href="#" class="btn btn-danger dropdown-toggle btn-sm" data-toggle="dropdown"><span class="fa-solid fa-trash-can"></span> Excluir Conta</a>
                <ul class="dropdown-menu">
                    <li>
                        <div class="notification_desc2">
                            <p class="mb-1">Confirmar Exclusão?<a href="#" onclick="deletarSel()" class="btn btn-danger btn-xs"><span class="fa fa-check"></span> Sim, Excluir</a></p>
                        </div>
                    </li>
                </ul>
            </li>
        </div>
        <div class="col-md-8 col-sm-12">
            <div class="row align-items-center">
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0"><label class="small text-muted mb-1">De:</label><input type="date" name="dataInicial" id="dataInicial" class="form-control form-control-sm" value="" onchange="buscarData()"></div>
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0"><label class="small text-muted mb-1">Até:</label><input type="date" name="dataFinal" id="dataFinal" class="form-control form-control-sm" value="" onchange="buscarData()"></div>
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0"><label class="small text-muted mb-1">Status:</label><select name="pago" id="pago" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        <option value="Sim">Pagas</option>
                        <option value="Não">Pendentes</option>
                    </select></div>
                <div class="col-md-3 col-sm-12 mb-2 mb-md-0"><label class="small text-muted mb-1">&nbsp;</label><span class="d-inline-flex align-items-center gap-1 text-nowrap filtro-rapido"><a href="#" onclick="trocarData('mes')" class="text-decoration-none small">Mês</a><span class="text-muted">|</span><a href="#" onclick="trocarData('hoje')" class="text-decoration-none small">Hoje</a><span class="text-muted">|</span><a href="#" onclick="trocarData('ontem')" class="text-decoration-none small">Ontem</a><span class="text-muted">|</span><a href="#" onclick="trocarData('amanha')" class="text-decoration-none small">Amanhã</a></span></div>
            </div>
        </div>
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
                <h4 class="modal-title"><span id="titulo_inserir"></span></h4><button id="btn-fechar" type="button" class="close mg-t--20" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="form" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5"><label for="descricao">Descrição</label><input type="text" class="form-control" id="descricao-perfil" name="descricao" required></div>
                        <div class="col-md-4"><label for="paciente">Paciente</label><select name="paciente" id="paciente-perfil" class="form-control">
                                <option value="" selected disabled>Escolha um paciente...</option><?php $query = $pdo->query("SELECT * FROM pacientes ORDER BY nome asc");
                                                                                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                                                                    $total_reg = @count($res);
                                                                                                    if ($total_reg > 0) {
                                                                                                        for ($i = 0; $i < $total_reg; $i++) {
                                                                                                            echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
                                                                                                        }
                                                                                                    } else {
                                                                                                        echo '<option value="0" disabled>Cadastre um Paciente</option>';
                                                                                                    } ?>
                            </select></div>
                        <div class="col-md-3"><label for="valor">Valor</label><input type="text" class="form-control moeda" id="valor-conta" name="valor" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"><label for="vencimento">Vencimento</label><input type="date" class="form-control" id="vencimento-conta" name="vencimento" value="<?php echo $data_atual ?>" required></div>
                        <div class="col-md-3"><label for="pago">Pago em</label><input type="date" class="form-control" id="pagamento-conta" name="pagamento"></div>
                        <div class="col-md-3"><label for="forma_pagamento">Forma de Pagamento</label><select class="form-control" name="forma_pagamento" id="forma_pagamento" required>
                                <option value="" selected disabled>Escolha uma forma de pagamento...</option><?php $query = $pdo->query("SELECT * FROM forma_pagamento ORDER BY nome asc");
                                                                                                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                                                                                $total_reg = @count($res);
                                                                                                                if ($total_reg > 0) {
                                                                                                                    for ($i = 0; $i < $total_reg; $i++) {
                                                                                                                        echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
                                                                                                                    }
                                                                                                                } else {
                                                                                                                    echo '<option value="0">Cadastre uma Forma de Pagamento</option>';
                                                                                                                } ?>
                            </select></div>
                        <div class="col-md-3"><label for="frequencia">Frequência</label><select name="frequencia" id="frequencia" class="form-control" required>
                                <option value="" selected disabled>Escolha uma frequência...</option><?php $query = $pdo->query("SELECT * FROM frequencias ORDER BY frequencia asc");
                                                                                                        $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                                                                                        $total_reg = @count($res);
                                                                                                        if ($total_reg > 0) {
                                                                                                            for ($i = 0; $i < $total_reg; $i++) {
                                                                                                                echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['frequencia'] . '</option>';
                                                                                                            }
                                                                                                        } else {
                                                                                                            echo '<option value="0">Cadastre uma Frequência</option>';
                                                                                                        } ?>
                            </select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-5"><label for="obs">Observações</label><input type="text" class="form-control" id="obs-perfil" name="obs" required></div>
                        <div class="col-md-5"><label for="arquivo">Arquivo</label><input type="file" class="form-control" id="arquivo-conta" name="arquivo" onchange="carregarImgReceber()"></div>
                        <div class="col-md-2"><img src="./images/receber/sem-foto.png" alt="Foto do arquivo" style="width: 80px;" id="target-arquivo"></div><input type="hidden" name="id" id="id">
                    </div>
                    <div class="row mt-3 p-3 bg-light rounded">
                        <div class="col-12"><b>Ajustes Financeiros (Opcional)</b></div>
                        <div class="col-md-3 mt-2"><label>Multa</label><input type="text" class="form-control moeda" name="multa" id="multa-perfil" placeholder="Auto"></div>
                        <div class="col-md-3 mt-2"><label>Juros</label><input type="text" class="form-control moeda" name="juros" id="juros-perfil" placeholder="Auto"></div>
                        <div class="col-md-3 mt-2"><label>Desconto</label><input type="text" class="form-control moeda" name="desconto" id="desconto-perfil" placeholder="Auto"></div>
                        <div class="col-md-3 mt-2"><label>Taxa</label><input type="text" class="form-control moeda" name="taxa" id="taxa-perfil" placeholder="R$ 2,50"></div>
                    </div>
                    <div id="mensagem" class="centro-pequeno"></div>
                </div>
                <div class="modal-footer centro"><button type="submit" class="btn btn-primary">Salvar</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Dados -->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title"><span id="titulo_dados"></span></h4><button id="btn-fechar-dados" type="button" class="close text-white mg-t--20" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row br-btt pb-2">
                    <div class="col-md-6"><span><b>Descrição: </b></span><span id="descricao_dados-cli"></span></div>
                    <div class="col-md-6"><span><b>Paciente: </b></span><span id="paciente_dados-cli"></span></div>
                </div>
                <div class="row br-btt pb-2">
                    <div class="col-md-4"><span><b>Valor: </b></span><span id="valor_dados-cli" class="text-success font-weight-bold"></span></div>
                    <div class="col-md-4"><span><b>Vencimento: </b></span><span id="vencimento_dados-cli"></span></div>
                    <div class="col-md-4"><span><b>Pago em: </b></span><span id="pagamento_dados-cli"></span></div>
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
                    <div class="col-12"><span><b>Arquivo/Comprovante: </b></span><br><a id="link-arquivo-dados" href="#" target="_blank" class="btn btn-sm btn-outline-primary mt-1"><i class="fa fa-eye"></i> Visualizar Arquivo</a><img id="target-arquivo-dados" src="./images/receber/sem-foto.png" alt="Comprovante" class="mt-2" style="max-width: 200px; border-radius: 4px;"></div>
                </div>
                <div class="row bg-light p-2 rounded">
                    <div class="col-md-3"><small class="text-muted">Multa</small><br><span id="multa_dados-cli" class="font-weight-bold"></span></div>
                    <div class="col-md-3"><small class="text-muted">Juros</small><br><span id="juros_dados-cli" class="font-weight-bold"></span></div>
                    <div class="col-md-3"><small class="text-muted">Desconto</small><br><span id="desconto_dados-cli" class="font-weight-bold"></span></div>
                    <div class="col-md-3"><small class="text-muted">Taxa</small><br><span id="taxa_dados-cli" class="font-weight-bold"></span></div>
                </div>
                <div class="row mt-2 bg-success text-white p-2 rounded text-center">
                    <div class="col-12"><b>Subtotal:</b> <span id="subtotal_dados-cli" class="font-weight-bold"></span></div>
                </div>
                <div class="row bg-light p-2 rounded mt-2">
                    <div class="col-md-6"><small class="text-muted">Lançado por:</small><br><span id="usuario_lanc_dados-cli" class="font-weight-bold"></span></div>
                    <div class="col-md-6"><small class="text-muted">Baixa por:</small><br><span id="usuario_pgto_dados-cli" class="font-weight-bold"></span></div>
                </div>
                <div class="row mt-2" id="row-referencia" style="display:none;">
                    <div class="col-12"><span><b>Referência: </b></span><span id="referencia_dados-cli"></span><small class="text-muted">(ID: <span id="id-referencia_dados-cli"></span>)</small></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button></div>
        </div>
    </div>
</div>

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
                        <div class="col-md-6"><label>Valor</label><input type="text" class="form-control moeda" id="valor-baixar" name="valor" readonly></div>
                        <div class="col-md-6"><label>Forma de Pagamento</label><select class="form-control" name="forma_pagamento" id="saida-baixar" required>
                                <option value="">Selecione...</option><?php $query = $pdo->query("SELECT * FROM forma_pagamento ORDER BY nome ASC");
                                                                        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $fp) {

                                                                            $taxa = 0;
                                                                            $nome = strtolower($fp['nome']);

                                                                            if (strpos($nome, 'débito') !== false || strpos($nome, 'debito') !== false) {
                                                                                $taxa = 3;
                                                                            } elseif (strpos($nome, 'crédito') !== false || strpos($nome, 'credito') !== false) {
                                                                                $taxa = 5;
                                                                            }

                                                                            echo "<option value='{$fp['id']}' data-taxa='{$taxa}'>{$fp['nome']}</option>";


                                                                            // echo "<option value='{$fp['id']}'>{$fp['nome']}</option>";
                                                                        } ?>
                            </select></div>
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
    });

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

    function editar(id, descricao, paciente, valor, data_vencimento, data_lancamento, data_pagamento, forma_pagamento, frequencia, obs, arquivo, multa, juros, desconto, taxa, subtotal) {
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

    function mostrar(descricao, paciente, valor, data_vencimento, data_lancamento, data_pagamento, forma_pagamento, frequencia, obs, arquivo, multa, juros, desconto, taxa, subtotal, usuario_lanc, usuario_pgto) {
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

    // ✅ LÓGICA DA MODAL DE BAIXA

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
</script>