<?php
require_once('../conexao.php');
require_once('verificar.php');

$pag = 'categorias_pecas';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_sistema ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/tabela_pequena.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>

<body>

    <a onclick="inserir()" href="#" type="button" class="btn btn-primary btn-sm">
        <span class="fas fa-list"></span>
        Categoria
    </a>

    <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
        <a href="#" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown">
            <span class="fa-solid fa-trash-can text-whiter"></span>
            Excluir Categoria
        </a>

        <ul class="dropdown-menu">

            <li>
                <div class="notification_desc2 centro">
                    <p class="mb-1">Confirmar Exclusão?
                        <a href="#" onclick="deletarSel()" class="btn btn-danger btn-xs">
                            <span class="fa fa-check"> Sim, Excluir</span>
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
            <div class="modal-header bg-success" style="color: black;">
                <h4 class="modal-title">
                    <i class="fas fa-list"></i>
                    <span id="titulo_inserir"></span>
                </h4>
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
                    </div>
                    <div id="mensagem" class="centro-pequeno"></div>
                </div>
                <div class="modal-footer centro">
                    <button type="submit" class="btn btn-primary btn-sm" id="btn_salvar">
                        Salvar
                    </button>
                    <input type="hidden" name="id" id="id">
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<!-- Fim Modal Inserir-->

<script src="../js/ajax.js"></script>
