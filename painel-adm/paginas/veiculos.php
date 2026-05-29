<?php
require_once('../conexao.php');
require_once('verificar.php');

$pag = 'veiculos';
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
            <span class="fas fa-car"></span>
            Veículo
        </a>

        <li class="dropdown head-dpdn2" style="display: inline-block;" id="btn-deletar">
            <a href="#" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown">
                <span class="fa-solid fa-trash-can text-whiter"></span>
                Excluir Veículo
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
                    <i class="fas fa-car"></i>&nbsp;<span id="titulo_inserir"></span>
                </h4>
                <button id="btn-fechar" type="button" class="close texto-preto" style="margin-top: -40px !important;" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="cliente_id">Cliente *</label>
                            <select class="form-control" id="cliente_id" name="cliente_id" required>
                                <option value="">Selecione...</option>
                                <?php
                                $clientes = $pdo->query("SELECT id, nome FROM clientes WHERE ativo = 'Sim' ORDER BY nome")->fetchAll();
                                foreach ($clientes as $c) { echo "<option value='{$c['id']}'>{$c['nome']}</option>"; }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="placa">Placa *</label>
                            <input type="text" class="form-control" id="placa" name="placa" required maxlength="10" placeholder="ABC1D23" style="text-transform:uppercase">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="marca">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca" maxlength="50">
                        </div>
                        <div class="col-md-4">
                            <label for="modelo">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" maxlength="80">
                        </div>
                        <div class="col-md-4">
                            <label for="ano">Ano</label>
                            <input type="text" class="form-control" id="ano" name="ano" maxlength="4" placeholder="2020">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="motor">Motor</label>
                            <input type="text" class="form-control" id="motor" name="motor" maxlength="50">
                        </div>
                        <div class="col-md-4">
                            <label for="cor">Cor</label>
                            <input type="text" class="form-control" id="cor" name="cor" maxlength="30" placeholder="Ex: Prata">
                        </div>
                        <div class="col-md-4">
                            <label for="km_atual">KM Atual</label>
                            <input type="number" class="form-control" id="km_atual" name="km_atual" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="observacoes">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
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
                    <i class="fas fa-car"></i>&nbsp;<span id="placa_dados-vei"></span>
                </h4>
                <button id="btn-fechar" type="button" class="close texto-preto" style="margin-top: -40px !important;" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row br-btt">
                    <div class="col-md-6"><b>Cliente: </b><span id="cliente_dados-vei"></span></div>
                    <div class="col-md-6"><b>Modelo: </b><span id="modelo_dados-vei"></span></div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4"><b>Marca: </b><span id="marca_dados-vei"></span></div>
                    <div class="col-md-4"><b>Ano: </b><span id="ano_dados-vei"></span></div>
                    <div class="col-md-4"><b>Cor: </b><span id="cor_dados-vei"></span></div>
                </div>
                <div class="row br-btt">
                    <div class="col-md-4"><b>Motor: </b><span id="motor_dados-vei"></span></div>
                    <div class="col-md-4"><b>KM Atual: </b><span id="km_dados-vei"></span></div>
                    <div class="col-md-4"><b>Cadastro: </b><span id="data_dados-vei"></span></div>
                </div>
                <div class="row">
                    <div class="col-md-12"><b>Observações: </b><span id="obs_dados-vei"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fim Modal Dados-->

<script src="../js/ajax.js"></script>
<!-- Máscara de Placa + Busca por Placa -->
<script src="../js/mascara-placa.js"></script>
<script>
$(document).ready(function() {
    // ✅ Busca automática ao digitar a placa (no modal de cadastro/edição)
    $('#placa').on('blur', function() {
        const placa = $(this).val().replace(/[^A-Z0-9]/g, '');
        if (placa.length >= 6) {
            $.getJSON('api/buscar_veiculo_por_placa.php', { placa }, function(data) {
                if (data.encontrado) {
                    if (confirm('Veículo encontrado!\n' + data.modelo + ' - ' + data.cliente_nome + '\nDeseja preencher os dados?')) {
                        $('#id').val(data.id);
                        $('#cliente_id').val(data.cliente_id);
                        $('#marca').val(data.marca);
                        $('#modelo').val(data.modelo);
                        $('#ano').val(data.ano);
                        $('#motor').val(data.motor);
                        $('#cor').val(data.cor || '');  // ✅ Cor adicionada
                        $('#km_atual').val(data.km_atual);
                        $('#observacoes').val(data.observacoes);
                    }
                }
            });
        }
    });

    // ✅ Também busca no autocomplete do orçamento
    if ($('#veiculo_placa').length) {
        $('#veiculo_placa').on('blur', function() {
            const placa = $(this).val().replace(/[^A-Z0-9]/g, '');
            if (placa.length >= 6) {
                $.getJSON('../api/buscar_veiculo_por_placa.php', { placa }, function(data) {
                    if (data.encontrado) {
                        $('#veiculo_id').val(data.id);
                        $('#veiculo_marca').val(data.marca);
                        $('#veiculo_modelo').val(data.modelo);
                        $('#veiculo_ano').val(data.ano);
                        $('#veiculo_motor').val(data.motor);
                        $('#veiculo_cor').val(data.cor || '');  // ✅ Cor adicionada
                        $('#veiculo_km').val(data.km_atual);
                        if ($('#cliente_id').length && data.cliente_id) {
                            $('#cliente_id').val(data.cliente_id);
                        }
                    }
                });
            }
        });
    }
});
</script>
