<?php
require_once('../conexao.php');
require_once('verificar.php');

$pag = 'usuarios';
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
        Usuário
    </a>

    <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
        <a href="#" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
            <span class="fa-solid fa-trash-can text-whiter"></span>
            Excluir User
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
                        <div class="col-md-6">
                            <label for="email">E-mail</label>
                            <input type="email" class="form-control" id="email-perfil" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="cpf">CPF</label>
                            <input type="text" class="form-control cpf" id="cpf-perfil" name="cpf" required>
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
                            <label for="senha">Senha</label>
                            <input type="password" class="form-control" id="senha-perfil" name="senha">
                        </div>
                        <div class="col-md-4">
                            <label for="conf-senha">Confirmar</label>
                            <input type="password" class="form-control" id="conf-senha-perfil" name="conf-senha">
                        </div>
                        <div class="col-md-4">
                            <label for="nivel">Nível</label>
                            <select class="form-control" name="nivel" id="nivel">
                                <?php
                                $query = $pdo->query("SELECT * FROM cargos ORDER BY nome asc");
                                $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                $total_reg = @count($res);
                                if ($total_reg > 0) {
                                    for ($i = 0; $i < $total_reg; $i++) {
                                        echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="0">Cadastre um Cargo</option>';
                                }
                                ?>
                            </select>
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
                        <div class="col-md-6">
                            <label for="foto">Foto</label>
                            <input type="file" class="form-control" id="foto-perfil" name="foto" onchange="carregarImgPerfil()">
                        </div>
                        <div class="col-md-2">
                            <img src="./images/perfil/sem-foto.jpg" alt="Foto do usuário" style="width: 80px;" id="target-usu">
                        </div>
                        <input type="hidden" name="id" id="id">
                    </div>

                    <!-- ✅ SEÇÃO FUNCIONÁRIO (campos adicionados) -->
                    <div class="row border-top pt-3 mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">👔 Dados de Funcionário <small class="text-muted">(Opcional)</small></h6>
                        </div>
                        <div class="col-md-4">
                            <label for="data_admissao">Data de Admissão</label>
                            <input type="date" class="form-control" id="data_admissao" name="data_admissao">
                        </div>
                        <div class="col-md-4">
                            <label for="cargo_funcional">Cargo na Clínica</label>
                            <input type="text" class="form-control" id="cargo_funcional" name="cargo_funcional" placeholder="Ex: Recepcionista, ASB...">
                        </div>
                        <div class="col-md-4">
                            <label for="tipo_contrato">Tipo de Contrato</label>
                            <select class="form-control" id="tipo_contrato" name="tipo_contrato">
                                <option value="CLT">CLT</option>
                                <option value="PJ">PJ</option>
                                <option value="Estagiário">Estagiário</option>
                                <option value="Autônomo">Autônomo</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label for="observacoes_func">Observações de RH</label>
                            <textarea class="form-control" id="observacoes_func" name="observacoes_func" rows="2" placeholder="Anotações internas..."></textarea>
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
<!-- Fim Modal Inserir-->

<!-- Modal Dados-->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h4 class="modal-title"><span id="nome_dados-cli"></span></h4>
                <button id="btn-fechar-dados-cli" type="button" class="close mg-t--20" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row br-btt">
                    <div class="col-md-6">
                        <span><b>Email: </b></span>
                        <span id="email_dados-cli"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Nível: </b></span>
                        <span id="cargo_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-6">
                        <span><b>CPF: </b></span>
                        <span id="cpf_dados-cli"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Telefone: </b></span>
                        <span id="telefone_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4">
                        <span><b>CEP: </b></span>
                        <span id="cep_dados-cli"></span>
                    </div>
                    <div class="col-md-8">
                        <span><b>Rua: </b></span>
                        <span id="rua_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4">
                        <span><b>Número: </b></span>
                        <span id="numero_dados-cli"></span>
                    </div>
                    <div class="col-md-8">
                        <span><b>Bairro: </b></span>
                        <span id="bairro_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-8">
                        <span><b>Cidade: </b></span>
                        <span id="cidade_dados-cli"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Estado: </b></span>
                        <span id="estado_dados-cli"></span>
                    </div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4">
                        <span><b>Ativo? </b></span>
                        <span id="ativo_dados-cli"></span>
                    </div>
                    <div class="col-md-4">
                        <span><b>Data Cadastro: </b></span>
                        <span id="data_dados-cli"></span>
                    </div>
                </div>
                <div class="row text-center mt-3">
                    <img id="foto_dados-cli" src="images/perfil/" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                </div>

                <!-- ✅ SEÇÃO FUNCIONÁRIO NO MODAL DE VISUALIZAÇÃO -->
                <div class="row border-top pt-3 mt-3">
                    <div class="col-12">
                        <h6 class="text-primary mb-3">👔 Dados de Funcionário</h6>
                    </div>
                    <div class="col-md-6">
                        <span><b>Data de Admissão: </b></span>
                        <span id="data_admissao_dados"></span>
                    </div>
                    <div class="col-md-6">
                        <span><b>Cargo: </b></span>
                        <span id="cargo_funcional_dados"></span>
                    </div>
                    <div class="col-md-6 mt-2">
                        <span><b>Tipo de Contrato: </b></span>
                        <span id="tipo_contrato_dados"></span>
                    </div>
                    <div class="col-12 mt-2">
                        <span><b>Observações de RH: </b></span>
                        <p class="small text-muted mb-0" id="observacoes_func_dados"></p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- Fim Modal Dados-->

<!-- Modal Permissões -->
<div class="modal fade" id="modalPermissoes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    Usuário: <span id="nome_permissoes"></span>
                    <span class="absolute-right">
                        <input class="form-check-input" type="checkbox" id="input_todos" onchange="marcarTodos()">
                        <label for="input_todos">Marcar Todos</label>
                    </span>
                </h4>
                <button type="button" class="close mg-t--20" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row" id="listar_permissoes">

                </div>
                <br>
                <input type="hidden" name="id" id="id_permissoes">
                <div id="mensagem_permissao" class="mt-3 centro-pequeno"></div>
            </div>

        </div>
    </div>
</div>
<!-- Fim Modal Permissões -->

<script src="../js/ajax.js"></script>