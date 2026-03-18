<?php
require_once('../conexao.php');
require_once('verificar.php');

$pag = 'pagar';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_sistema ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
        /* === Apenas a tabela #tabela e seus controles DataTables === */
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

    <a onclick="inserir()" href="#" type="button" class="btn btn-primary">
        <span class="fa fa-plus"></span>
        Conta
    </a>

    <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
        <a href="#" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
            <span class="fa-solid fa-trash-can text-whiter"></span>
            Excluir Conta
        </a>
        <ul class="dropdown-menu">
            <li>
                <div class="notification_desc2">
                    <p>Confirmar Exclusão?
                        <a href="#" onclick="deletarSel()">
                            <span class="text-danger">Sim</span>
                        </a>
                    </p>
                </div>
            </li>
        </ul>
    </li>

    <div class="bs-example widget-shadow table-primary" id="listar"></div>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        var pag = "<?php echo $pag; ?>"
    </script>
    <input type="hidden" id="ids">

</body>

</html>

<!-- Modal Inserir-->
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
                            <label for="paciente">Paciente</label>
                            <select name="paciente" id="paciente-perfil" class="form-control">
                                <option value="" selected disabled>Escolha um paciente...</option>
                                <?php
                                $query = $pdo->query("SELECT * FROM pacientes ORDER BY nome asc");
                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                $total_reg = @count($res);
                                if ($total_reg > 0) {
                                    for ($i = 0; $i < $total_reg; $i++) {
                                        echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="0" disabled>Cadastre um Paciente</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="valor">Valor</label>
                            <input type="text" class="form-control" id="valor-conta" name="valor" required>
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
                            <input type="file" class="form-control" id="arquivo-conta" name="arquivo" onchange="carregarImgReceber()">
                        </div>
                        <div class="col-md-2">
                            <img src="./images/receber/sem-foto.png" alt="Foto do arquivo" style="width: 80px;" id="target-arquivo">
                        </div>
                        <input type="hidden" name="id" id="id">
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
<!-- Fim Modal Inserir-->

<!-- Modal Dados (Visualizar)-->
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

                <!-- Dados Principais -->
                <div class="row br-btt pb-2">
                    <div class="col-md-6">
                        <span><b>Descrição: </b></span>
                        <span id="descricao_dados-cli"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Paciente: </b></span>
                        <span id="paciente_dados-cli"></span>
                    </div>
                </div>

                <!-- Valores e Datas -->
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

                <!-- Forma de Pagamento e Frequência -->
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

                <!-- Lançamento e Observações -->
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

                <!-- Arquivo/Comprovante -->
                <div class="row br-btt pb-2">
                    <div class="col-12">
                        <span><b>Arquivo/Comprovante: </b></span><br>
                        <a id="link-arquivo-dados" href="#" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                            <i class="fa fa-eye"></i> Visualizar Arquivo
                        </a>
                        <img id="target-arquivo-dados" src="images/receber/sem-foto.png"
                            alt="Comprovante" class="mt-2" style="max-width: 200px; border-radius: 4px;">
                    </div>
                </div>

                <!-- Dados Financeiros (Multa/Juros/Desconto) -->
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
                        <small class="text-muted">Subtotal</small><br>
                        <span id="subtotal_dados-cli" class="font-weight-bold text-success"></span>
                    </div>
                </div>

                <!-- Referência (se houver) -->
                <div class="row mt-2" id="row-referencia" style="display:none;">
                    <div class="col-12">
                        <span><b>Referência: </b></span>
                        <span id="referencia_dados-cli"></span>
                        <small class="text-muted">(ID: <span id="id-referencia_dados-cli"></span>)</small>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<!-- Fim Modal Dados -->

<script src="../js/ajax.js"></script>

<!-- Função para formatar o valor para moeda brasileira -->
<script>
    function formatarMoedaInput(input) {
        let valor = input.value.replace(/\D/g, ""); // só números
        valor = (valor / 100).toFixed(2) + ""; // duas casas decimais
        valor = valor.replace(".", ","); // vírgula como separador decimal
        valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, "."); // pontos como separadores de milhar
        input.value = "R$ " + valor;
    }

    // Aplicar nos inputs
    document.getElementById("valor-conta").addEventListener("input", function() {
        formatarMoedaInput(this);
    });
</script>
<!-- Fim Função para formatar o valor para moeda brasileira -->