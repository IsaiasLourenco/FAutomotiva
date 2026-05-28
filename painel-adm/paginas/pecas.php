<?php
require_once('../conexao.php');
require_once('verificar.php');

$pag = 'pecas';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_sistema ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/tabela_pequena.css">
</head>

<body>
    <div class="main-page margin-mobile">
        <a onclick="inserir()" href="#" type="button" class="btn btn-primary btn-sm">
            <span class="fas fa-cogs"></span>
            Peça
        </a>

        <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
            <a href="#" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown">
                <span class="fa-solid fa-trash-can text-whiter"></span>
                Excluir Peça
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
    </div>
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
            <div class="modal-header bg-success" style="color: black">
                <h4 class="modal-title">
                    <i class="fas fa-cogs"></i>&nbsp;<span id="titulo_inserir"></span>
                </h4>
                <button id="btn-fechar" type="button" class="close texto-preto" style="margin-top: -40px !important;" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="nome_padrao">Nome da Peça *</label>
                            <input type="text" class="form-control" id="nome_padrao" name="nome_padrao" required maxlength="150" placeholder="Ex: Pastilha de Freio Dianteira">
                        </div>
                        <div class="col-md-6">
                            <label for="categoria">Categoria</label>
                            <select class="form-control" id="categoria" name="categoria">
                                <option value="">Selecione...</option>
                                <?php
                                $cats = $pdo->query("SELECT id, nome FROM categorias_pecas WHERE ativo = 'Sim' ORDER BY nome")->fetchAll();
                                foreach ($cats as $c) { echo "<option value='{$c['nome']}'>{$c['nome']}</option>"; }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="marca_recomendada_id">Marca Recomendada</label>
                            <select class="form-control" id="marca_recomendada_id" name="marca_recomendada_id">
                                <option value="">Selecione...</option>
                                <?php
                                $marcas = $pdo->query("SELECT id, nome FROM marcas WHERE ativo = 1 ORDER BY nota_qualidade DESC, nome")->fetchAll();
                                foreach ($marcas as $m) { echo "<option value='{$m['id']}'>{$m['nome']} ({$m['nota_qualidade']}/10)</option>"; }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="codigo_interno">Código Interno</label>
                            <input type="text" class="form-control" id="codigo_interno" name="codigo_interno" maxlength="50" placeholder="Ex: PF-VW-001">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="ativo">Ativo</label>
                            <select class="form-control" name="ativo" id="ativo">
                                <option value="Sim" selected>Ativo</option>
                                <option value="Não">Inativo</option>
                            </select>
                        </div>
                        <input type="hidden" name="id" id="id">
                    </div>
                    <div id="mensagem" class="centro-pequeno"></div>
                </div>
                <div class="modal-footer centro">
                    <button type="submit" class="btn btn-primary btn-sm" id="btn_salvar">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Fim Modal Inserir-->

<!-- Modal Dados (simplificado para peças) -->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success" style="color:black;">
                <h4 class="modal-title"><i class="fas fa-cogs"></i>&nbsp;<span id="nome_dados-pecas"></span></h4>
                <button type="button" class="close texto-preto" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row br-btt"><div class="col-md-6"><b>Categoria:</b> <span id="categoria_dados-pecas"></span></div></div>
                <div class="row br-btt"><div class="col-md-6"><b>Marca:</b> <span id="marca_dados-pecas"></span></div></div>
                <div class="row br-btt"><div class="col-md-6"><b>Código:</b> <span id="codigo_dados-pecas"></span></div></div>
                <div class="row"><div class="col-md-6"><b>Ativo:</b> <span id="ativo_dados-pecas"></span></div></div>
            </div>
        </div>
    </div>
</div>
<!-- Fim Modal Dados-->

<script src="../js/ajax.js"></script>
