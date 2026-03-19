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
    <!-- ✅ Cabeçalho: Botões + Filtros de Data -->
    <div class="row mb-3 align-items-center">

        <!-- Botões de Ação (Esquerda) -->
        <div class="col-md-4 col-sm-12 mb-2 mb-md-0">
            <!-- Botão Inserir -->
            <a onclick="inserir()" href="#" class="btn btn-primary mr-3 btn-sm">
                <span class="fa fa-plus"></span> Conta
            </a>

            <!-- Botão Excluir (dropdown) -->
            <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
                <a href="#" class="btn btn-danger dropdown-toggle btn-sm" data-toggle="dropdown">
                    <span class="fa-solid fa-trash-can"></span>
                    Excluir Conta
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <div class="notification_desc2">
                            <p class="mb-1">Confirmar Exclusão?</p>
                            <a href="#" onclick="deletarSel()" class="btn btn-danger btn-xs">
                                <span class="fa fa-check"></span> Sim, Excluir
                            </a>
                        </div>
                    </li>
                </ul>
            </li>
        </div>

        <!-- Filtros de Data (Direita) -->
        <div class="col-md-8 col-sm-12">
            <div class="row align-items-center"> <!-- ✅ MUDANÇA 1: center em vez de end -->

                <!-- Data Inicial -->
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">De:</label>
                    <input type="date" name="dataInicial" id="dataInicial"
                        class="form-control form-control-sm" value="<?php echo $data_inicio_mes ?>" onchange="buscarData()">
                </div>

                <!-- Data Final -->
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Até:</label>
                    <input type="date" name="dataFinal" id="dataFinal"
                        class="form-control form-control-sm" value="<?php echo $data_final_mes ?>" onchange="buscarData()">
                </div>

                <!-- Status -->
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Status:</label>
                    <select name="pago" id="pago" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        <option value="Sim">Pagas</option>
                        <option value="Não">Pendentes</option>
                    </select>
                </div>

                <!-- Links de Filtro Rápido -->
                <div class="col-md-3 col-sm-12 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">&nbsp;</label>
                    <span class="d-inline-flex align-items-center gap-1 text-nowrap  filtro-rapido"> <!-- ✅ MUDANÇA 2: alinha links -->
                        <a href="#" onclick="trocarData('mes')" class="text-decoration-none small">Mês</a>
                        <span class="text-muted">|</span>
                        <a href="#" onclick="trocarData('hoje')" class="text-decoration-none small">Hoje</a>
                        <span class="text-muted">|</span>
                        <a href="#" onclick="trocarData('ontem')" class="text-decoration-none small">Ontem</a>
                        <span class="text-muted">|</span>
                        <a href="#" onclick="trocarData('amanha')" class="text-decoration-none small">Amanhã</a>
                    </span>
                </div>

                <!-- Botão Filtrar -->
                <div class="col-md-2 col-sm-12 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-sm btn-block" onclick="filtrarPorData()">
                        <span class="fa fa-filter"></span>&nbsp;Filtrar
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- ✅ Script para filtrar por data -->
    <script type="text/javascript">
        function filtrarPorData() {
            var dataDe = $('#filtro-data-de').val();
            var dataAte = $('#filtro-data-ate').val();

            // Chama a função listar() do ajax.js passando os filtros
            // Se sua função listar() aceitar parâmetros, use:
            listar(dataDe, dataAte);

            // Se NÃO aceitar, você pode recarregar a tabela via AJAX customizado:
            /*
            $.ajax({
                url: 'paginas/' + pag + "/listar.php",
                method: 'POST',
                data: { data_de: dataDe, data_ate: dataAte },
                dataType: "html",
                success: function(result) {
                    $("#listar").html(result);
                    // Reinicializa DataTable se necessário
                    if ($.fn.DataTable.isDataTable('#tabela')) {
                        $('#tabela').DataTable().destroy();
                    }
                    $('#tabela').DataTable({
                        "ordering": false,
                        "stateSave": true,
                        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json" },
                        "columnDefs": [{ "className": "dt-center", "targets": "_all" }]
                    });
                    $('#tabela_wrapper').addClass('tabela-pequena');
                }
            });
            */
        }

        // Opcional: Filtrar ao pressionar Enter nos campos de data
        $('#filtro-data-de, #filtro-data-ate').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                filtrarPorData();
            }
        });
    </script>

    <div class="bs-example widget-shadow table-primary" id="listar"></div>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        var pag = "<?php echo $pag; ?>"
    </script>
    <input type="hidden" id="ids">

</body>

</html>

<!-- Modal Inserir/Editar-->
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
                    <!-- Dados Principais -->
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
                            <input type="text" class="form-control moeda" id="valor-conta" name="valor" required>
                        </div>
                    </div>

                    <!-- Datas e Formas -->
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

                    <!-- Observações e Arquivo -->
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

                    <!-- ✅ Ajustes Financeiros (Override Manual) -->
                    <div class="row mt-3 p-3 bg-light rounded">
                        <div class="col-12"><b>Ajustes Financeiros (Opcional)</b></div>
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
<!-- Fim Modal Inserir/Editar-->

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
                        <img id="target-arquivo-dados" src="./images/receber/sem-foto.png"
                            alt="Comprovante" class="mt-2" style="max-width: 200px; border-radius: 4px;">
                    </div>
                </div>

                <!-- Dados Financeiros (Multa/Juros/Desconto/Taxa) -->
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

                <!-- Subtotal (destacado) -->
                <div class="row mt-2 bg-success text-white p-2 rounded text-center">
                    <div class="col-12">
                        <b>Subtotal:</b> <span id="subtotal_dados-cli" class="font-weight-bold"></span>
                    </div>
                </div>

                <!-- Usuários (Lançamento e Pagamento) -->
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

<!-- ✅ Função para formatar moeda brasileira -->
<script>
    function formatarMoedaInput(input) {
        let valor = input.value.replace(/\D/g, ""); // só números
        valor = (valor / 100).toFixed(2) + ""; // duas casas decimais
        valor = valor.replace(".", ","); // vírgula como separador decimal
        valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, "."); // pontos como separadores de milhar
        input.value = "R$ " + valor;
    }

    // Aplicar formatação ao campo principal
    document.getElementById("valor-conta")?.addEventListener("input", function() {
        formatarMoedaInput(this);
    });

    // ✅ Aplicar formatação a TODOS os inputs com classe "moeda"
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".moeda").forEach(input => {
            input.addEventListener("input", function() {
                formatarMoedaInput(this);
            });
            // Formata também ao perder o foco (blur)
            input.addEventListener("blur", function() {
                if (this.value.trim() === "" || this.value === "R$ ") {
                    this.value = "R$ 0,00";
                }
            });
        });
    });

    function buscarData() {
        dataInicial = $('#dataInicial').val();
        dataFinal = $('#dataFinal').val();
        statusPago = $('#pago').val();
        tipoData = $('#tipoData').val();

        listar(dataInicial, dataFinal, statusPago, tipoData);
    }

    function trocarData(tipo) {

        data_inicio_mes = "<?php echo $data_inicio_mes ?>";
        data_final_mes = "<?php echo $data_final_mes ?>";
        data_atual = "<?php echo $data_atual ?>";
        data_ontem = "<?php echo $data_ontem ?>";
        data_amanha = "<?php echo $data_amanha ?>";

        if (tipo == 'mes') {
            $('#dataInicial').val(data_inicio_mes);
            $('#dataFinal').val(data_final_mes);
        }

        if (tipo == 'hoje') {
            $('#dataInicial').val(data_atual);
            $('#dataFinal').val(data_atual);
        }

        if (tipo == 'ontem') {
            $('#dataInicial').val(data_ontem);
            $('#dataFinal').val(data_ontem);
        }

        if (tipo == 'amanha') {
            $('#dataInicial').val(data_amanha);
            $('#dataFinal').val(data_amanha);
        }
        buscarData();
    }

    function porData(tipo) {
        $('#tipoData').val(tipo);
        buscarData();
    }

    // ✅ Filtrar automaticamente ao mudar o status (Sem/ Pagas/ Pendentes)
    $('#pago').on('change', function() {
        buscarData(); // Já chama listar() com os filtros
    });

    // ✅ Filtrar ao pressionar Enter nos campos de data
    $('#dataInicial, #dataFinal').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarData();
        }
    });
</script>