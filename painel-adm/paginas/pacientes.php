<?php
require_once('../conexao.php');
require_once('verificar.php');

$pag = 'pacientes';
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

    <a onclick="inserir()" href="#" type="button" class="btn btn-primary">
        <span class="fa fa-plus"></span>
        Paciente
    </a>

    <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
        <a href="#" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
            <span class="fa-solid fa-trash-can text-whiter"></span>
            Excluir Paciente
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
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title"><span id="titulo_inserir"></span></h4>
                <button id="btn-fechar" type="button" class="close mg-t--20" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="nome">Nome</label>
                            <input type="text" class="form-control" id="nome-paciente" name="nome" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email">E-mail</label>
                            <input type="email" class="form-control" id="email-paciente" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="cpf">CPF</label>
                            <input type="text" class="form-control cpf" id="cpf-paciente" name="cpf" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" id="telefone-paciente" name="telefone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label for="cep">CEP</label>
                            <input type="text" class="form-control" id="cep-paciente" name="cep" required>
                        </div>
                        <div class="col-md-5">
                            <label for="rua">Rua</label>
                            <input type="text" class="form-control" id="rua-paciente" name="rua" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="numero">Número</label>
                            <input type="text" class="form-control" id="numero-paciente" name="numero" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label for="bairro">Bairro</label>
                            <input type="text" class="form-control" id="bairro-paciente" name="bairro" readonly>
                        </div>
                        <div class="col-md-5">
                            <label for="cidade">Cidade</label>
                            <input type="text" class="form-control" id="cidade-paciente" name="cidade" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="estado">Estado</label>
                            <input type="text" class="form-control" id="estado-paciente" name="estado" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="foto">Foto</label>
                            <input type="file" class="form-control" id="foto-paciente" name="foto" onchange="carregarImgPaciente()">
                        </div>
                        <div class="col-md-2">
                            <img src="./images/perfil/sem-foto.jpg" alt="Foto do paciente" style="width: 80px;" id="target-paciente">
                        </div>
                        <input type="hidden" name="id" id="id">
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
<!-- Fim Modal Inserir-->

<!-- Modal Dados-->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">
                    <span id="nome_dados-paciente"></span>
                </h4>
                <button id="btn-fechar-dados-paciente" type="button" class="close mg-t--20" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row br-btt">
                    <div class="col-md-6">
                        <span><b>Email: </b></span>
                        <span id="email_dados-paciente"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-6">
                        <span><b>CPF: </b></span>
                        <span id="cpf_dados-paciente"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Telefone: </b></span>
                        <span id="telefone_dados-paciente"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4">
                        <span><b>CEP: </b></span>
                        <span id="cep_dados-paciente"></span>
                    </div>
                    <div class="col-md-8">
                        <span><b>Rua: </b></span>
                        <span id="rua_dados-paciente"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4">
                        <span><b>Número: </b></span>
                        <span id="numero_dados-paciente"></span>
                    </div>
                    <div class="col-md-8">
                        <span><b>Bairro: </b></span>
                        <span id="bairro_dados-paciente"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-8">
                        <span><b>Cidade: </b></span>
                        <span id="cidade_dados-paciente"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Estado: </b></span>
                        <span id="estado_dados-paciente"></span>
                    </div>
                </div>
                <div class="row text-center mt-3">
                    <img id="foto_dados-paciente" src="images/perfil/" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fim Modal Dados-->

<!-- Modal Contas -->
<div class="modal fade" id="modalContas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">
                    Contas de: <span id="titulo_contas"></span>
                </h4>
                <select id="filtro_status_contas" class="form-control form-control-sm" style="width: auto; margin-left: 50px;">
                    <option value="todas">Todas</option>
                    <option value="pendentes">Pendentes</option>
                    <option value="vencidas">Vencidas</option>
                    <option value="pagas">Pagas</option>
                </select>
                <button id="btn-fechar-contas" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -25px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="listar_debitos" class="mt-3"></div>
                <input type="hidden" id="id_contas">
            </div>
        </div>
    </div>
</div>
<!-- Fim Modal Contas -->

<script src="../js/ajax.js"></script>