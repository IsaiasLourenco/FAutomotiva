<?php
require_once('../conexao.php');
require_once('verificar.php');

$pag = 'grupo_acessos';
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

        /* A própria tabela */
        #tabela.tabela-pequena,
        #tabela.tabela-pequena th,
        #tabela.tabela-pequena td {
            font-size: 10px !important;
        }

        /* Wrapper do DataTables específico para #tabela */
        #tabela_wrapper {
            font-size: 10px !important;
            line-height: 1.4 !important;
        }

        /* Controles: busca, seleção de registros */
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

        /* Labels dos controles */
        #tabela_wrapper .dataTables_length label,
        #tabela_wrapper .dataTables_filter label {
            font-size: 10px !important;
            margin: 0 !important;
            font-weight: normal !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        /* Texto de informação */
        #tabela_wrapper .dataTables_info {
            font-size: 10px !important;
            padding-top: 5px !important;
            line-height: 1.4 !important;
        }

        /* Botões de paginação */
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

        /* Ajuste de layout para os controles */
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

        /* Responsivo */
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
        Grupo
    </a>

    <li class="dropdown head-dpdn2" style="display: inline-block;"  id="btn-deletar">
        <a href="#" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
            <span class="fa-solid fa-trash-can text-whiter"></span>
                Excluir Grupo
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

    <div class="bs-example widget-shadow table-primary" id="listar">

    </div>

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
    <div class="modal-dialog" role="document">
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
                        <div class="col-md-6">
                            <label for="nome">Nome</label>
                            <input type="text" class="form-control" id="nome-perfil" name="nome" required>
                        </div>
                        <div class="col-md-6 mt-22">
                            <button type="submit" class="btn btn-primary" id="btn_salvar">
                                Salvar
                            </button>
                            <input type="hidden" name="id" id="id">
                        </div>
                    </div>
                    <div id="mensagem" class="centro-pequeno"></div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<!-- Fim Modal Inserir-->

    <script src="../js/ajax.js"></script>