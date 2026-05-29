<?php
require_once('../conexao.php');
require_once('verificar.php');

$pag = 'fornecedores';
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
            <span class="fas fa-truck"></span>
            Fornecedor
        </a>

        <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
            <a href="#" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown">
                <span class="fa-solid fa-trash-can text-whiter"></span>
                Excluir Fornecedor
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
            <div class="modal-header bg-success" style="color: black;">
                <h4 class="modal-title">
                    <i class="fas fa-truck"></i>&nbsp;<span id="titulo_inserir"></span>
                </h4>
                <button id="btn-fechar" type="button" class="close texto-preto" style="margin-top: -40px !important;" data-dismiss="modal" aria-label="Close">
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
                        <div class="col-md-6">
                            <label for="email">E-mail</label>
                            <input type="email" class="form-control" id="email-perfil" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="cnpj">CNPJ</label>
                            <input type="text" class="form-control cnpj" id="cnpj-perfil" name="cnpj" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" id="telefone-perfil" name="telefone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label for="cep">CEP</label>
                            <input type="text" class="form-control" id="cep-perfil" name="cep" required>
                        </div>
                        <div class="col-md-5">
                            <label for="rua">Rua</label>
                            <input type="text" class="form-control" id="rua-perfil" name="rua" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="numero">Número</label>
                            <input type="text" class="form-control" id="numero-perfil" name="numero" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label for="bairro">Bairro</label>
                            <input type="text" class="form-control" id="bairro-perfil" name="bairro" readonly>
                        </div>
                        <div class="col-md-5">
                            <label for="cidade">Cidade</label>
                            <input type="text" class="form-control" id="cidade-perfil" name="cidade" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="estado">Estado</label>
                            <input type="text" class="form-control" id="estado-perfil" name="estado" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="ativo">Ativo</label>
                            <select class="form-control" name="ativo" id="ativo">
                                <option value="Sim" selected>Sim</option>
                                <option value="Não">Não</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="obs">Observações</label>
                            <textarea class="form-control" id="obs-perfil" name="obs" rows="3"></textarea>
                        </div>
                        <input type="hidden" name="id" id="id">
                    </div>
                    <div id="mensagem" class="centro-pequeno"></div>
                </div>
                <div class="modal-footer centro">
                    <button type="submit" class="btn btn-primary btn-sm" id="btn_salvar">
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
            <div class="modal-header bg-success" style="color: black;">
                <h4 class="modal-title">
                    <i class="fas fa-truck"></i>&nbsp;<span id="nome_dados-for"></span>
                </h4>
                <button id="btn-fechar"
                        type="button"
                        class="close texto-preto"
                        style="margin-top: -40px !important;"
                        data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row br-btt">
                    <div class="col-md-6">
                        <span><b>Email: </b></span>
                        <span id="email_dados-for"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-6">
                        <span><b>CNPJ: </b></span>
                        <span id="cnpj_dados-for"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Telefone: </b></span>
                        <span id="telefone_dados-for"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4">
                        <span><b>CEP: </b></span>
                        <span id="cep_dados-for"></span>
                    </div>
                    <div class="col-md-8">
                        <span><b>Rua: </b></span>
                        <span id="rua_dados-for"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4">
                        <span><b>Número: </b></span>
                        <span id="numero_dados-for"></span>
                    </div>
                    <div class="col-md-8">
                        <span><b>Bairro: </b></span>
                        <span id="bairro_dados-for"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-8">
                        <span><b>Cidade: </b></span>
                        <span id="cidade_dados-for"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Estado: </b></span>
                        <span id="estado_dados-for"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4">
                        <span><b>Ativo? </b></span>
                        <span id="ativo_dados-for"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Data Cadastro: </b></span>
                        <span id="data_dados-for"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span><b>Observações: </b></span>
                        <span id="obs_dados-for"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fim Modal Dados-->

<script src="../js/ajax.js"></script>
