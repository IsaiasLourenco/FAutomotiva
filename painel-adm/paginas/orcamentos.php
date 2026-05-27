<?php
// FAutomotiva/painel-adm/paginas/orcamento.php
// Verifica se está logado
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

// Busca fornecedores ativos
$fornecedores = $pdo->query("SELECT id, nome FROM fornecedores WHERE ativo = 'Sim' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$num_fornecedores = count($fornecedores);
?>
<link rel="stylesheet" href="../css/orcamento.css">
<link rel="stylesheet" href="../css/style.css">
<div class="checklist-container">
    <form id="form-orcamento" method="POST">

        <!-- HEADER -->
        <div class="checklist-header text-center">
            CHECK-LIST &nbsp;&nbsp;&nbsp;
            <span style="font-size: 18px;">DATA: __/__/____</span>
        </div>

        <!-- DADOS DO CLIENTE -->
        <div class="row form-row">
            <div class="col-md-6">
                <label>NOME:</label>
                <input type="text" class="form-control form-control-checklist" id="cliente_nome" name="cliente_nome" required>
                <input type="hidden" id="cliente_id" name="cliente_id">
            </div>
            <div class="col-md-3">
                <label>CPF:</label>
                <input type="text" class="form-control form-control-checklist cpf" id="cliente_cpf" name="cliente_cpf">
            </div>
            <div class="col-md-3">
                <label>CEL:</label>
                <input type="text" class="form-control form-control-checklist" id="cliente_cel" name="cliente_cel" placeholder="(__) _____-____">
            </div>
        </div>

        <div class="row form-row">
            <div class="col-md-3">
                <label>CEP:</label>
                <input type="text" class="form-control form-control-checklist" id="cep" name="cep" placeholder="_____-___">
            </div>
            <div class="col-md-2">
                <label>N/:</label>
                <input type="text" class="form-control form-control-checklist" id="numero" name="numero">
            </div>
            <div class="col-md-7">
                <label>RUA:</label>
                <input type="text" class="form-control form-control-checklist" id="rua" name="rua" readonly>
            </div>
        </div>

        <!-- DADOS DO VEÍCULO -->
        <div class="row form-row">
            <div class="col-md-4">
                <label>CARRO:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_modelo" name="veiculo_modelo" required>
                <input type="hidden" id="veiculo_id" name="veiculo_id">
            </div>
            <div class="col-md-2">
                <label>PLACA:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_placa" name="veiculo_placa" maxlength="8" style="text-transform:uppercase" required>
            </div>
            <div class="col-md-2">
                <label>KM:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_km" name="veiculo_km">
            </div>
            <div class="col-md-2">
                <label>MOTOR:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_motor" name="veiculo_motor">
            </div>
            <div class="col-md-2">
                <label>ANO:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_ano" name="veiculo_ano">
            </div>
        </div>

        <div class="row form-row">
            <div class="col-md-3">
                <label>COR:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_cor" name="veiculo_cor">
            </div>
            <div class="col-md-3">
                <label>DIR:</label>
                <select class="form-control form-control-checklist" id="veiculo_direcao" name="veiculo_direcao">
                    <option value="">SELECIONE</option>
                    <option value="HID">HID</option>
                    <option value="ELE">ELE</option>
                    <option value="MEC">MEC</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>HR:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_hr" name="veiculo_hr" placeholder="( )">
            </div>
        </div>

        <!-- SERVIÇOS INICIAIS -->
        <div class="row form-row">
            <div class="col-md-3">
                <label>ALINHAMENTO:</label>
                <select class="form-control form-control-checklist" name="alinhamento">
                    <option value="">NÃO</option>
                    <option value="D">D</option>
                    <option value="T">T</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>BALANCEAMENTO:</label>
                <select class="form-control form-control-checklist" name="balanceamento">
                    <option value="">NÃO</option>
                    <option value="D">D</option>
                    <option value="T">T</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>CAMB:</label>
                <select class="form-control form-control-checklist" name="camb">
                    <option value="">NÃO</option>
                    <option value="DD">DD</option>
                    <option value="DE">DE</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>ALINHAM TRZ:</label>
                <select class="form-control form-control-checklist" name="alinham_trz">
                    <option value="">NÃO</option>
                    <option value="D">D</option>
                    <option value="T">T</option>
                </select>
            </div>
        </div>

        <div class="row form-row">
            <div class="col-md-4">
                <label>MONTAGEM:</label>
                <input type="text" class="form-control form-control-checklist" name="montagem">
            </div>
            <div class="col-md-4">
                <label>BICO:</label>
                <input type="text" class="form-control form-control-checklist" name="bico">
            </div>
            <div class="col-md-4">
                <label>VL=:</label>
                <input type="text" class="form-control form-control-checklist moeda" name="vl_bico" placeholder="R$ 0,00">
            </div>
        </div>

        <!-- PNEUS -->
        <div class="row form-row">
            <div class="col-md-4">
                <label>PNEUS - MARCA:</label>
                <input type="text" class="form-control form-control-checklist" name="pneus_marca">
            </div>
            <div class="col-md-4">
                <label>QUANT:</label>
                <input type="text" class="form-control form-control-checklist" name="pneus_quant">
            </div>
            <div class="col-md-4">
                <label>VL=:</label>
                <input type="text" class="form-control form-control-checklist moeda" name="pneus_vl" placeholder="R$ 0,00">
            </div>
        </div>

        <!-- TABELA DE PEÇAS/SERVIÇOS -->
        <table class="table table-checklist">
            <thead>
                <tr>
                    <th style="width: 30%;">M.O / MEC</th>
                    <th style="width: 10%;">SUSP DIAN</th>
                    <th style="width: 10%;">SUSP TR</th>
                    <th style="width: 10%;">M.O / S.P.</th>
                    <?php foreach ($fornecedores as $fornecedor): ?>
                        <th style="width: <?php echo 30/$num_fornecedores; ?>%;"><?php echo $fornecedor['nome']; ?></th>
                    <?php endforeach; ?>
                    <th style="width: 5%;">AC</th>
                    <th style="width: 5%;">MEN</th>
                    <th style="width: 5%;">AZ</th>
                    <th style="width: 10%;">TOTAL</th>
                </tr>
            </thead>
            <tbody id="itens-orcamento">
                <?php for ($i = 0; $i < 20; $i++): ?>
                <tr>
                    <td><input type="text" name="mec[<?php echo $i; ?>]" placeholder="MECÂN:"></td>
                    <td>
                        <select name="susp_dian[<?php echo $i; ?>]" class="form-control form-control-sm">
                            <option value=""></option>
                            <option value="DD">DD</option>
                            <option value="DE">DE</option>
                            <option value="TD">TD</option>
                            <option value="TE">TE</option>
                        </select>
                    </td>
                    <td>
                        <select name="susp_tr[<?php echo $i; ?>]" class="form-control form-control-sm">
                            <option value=""></option>
                            <option value="DD">DD</option>
                            <option value="DE">DE</option>
                            <option value="TD">TD</option>
                            <option value="TE">TE</option>
                        </select>
                    </td>
                    <td><input type="text" name="mo_sp[<?php echo $i; ?>]"></td>
                    <?php foreach ($fornecedores as $fornecedor): ?>
                        <td><input type="text" name="fornecedor[<?php echo $fornecedor['id']; ?>][<?php echo $i; ?>]" class="moeda-input"></td>
                    <?php endforeach; ?>
                    <td><input type="text" name="ac[<?php echo $i; ?>]"></td>
                    <td><input type="text" name="men[<?php echo $i; ?>]"></td>
                    <td><input type="text" name="az[<?php echo $i; ?>]"></td>
                    <td><input type="text" name="total[<?php echo $i; ?>]" class="total-item" readonly></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- ÓLEO E FLUIDOS -->
        <div class="row form-row" style="margin-top: 20px;">
            <div class="col-md-4">
                <label>OL/MOTOR:</label>
                <input type="text" class="form-control form-control-checklist" name="ol_motor" placeholder="__W">
            </div>
            <div class="col-md-3">
                <label>QUAN:</label>
                <input type="text" class="form-control form-control-checklist" name="ol_motor_quan" placeholder="__ / __">
            </div>
            <div class="col-md-3">
                <label>FIL ( ):</label>
                <input type="text" class="form-control form-control-checklist" name="filtro">
            </div>
        </div>

        <div class="row form-row">
            <div class="col-md-3">
                <label>F.COMB: ( )</label>
                <input type="text" class="form-control form-control-checklist" name="f_comb">
            </div>
            <div class="col-md-3">
                <label>F.AR: ( )</label>
                <input type="text" class="form-control form-control-checklist" name="f_ar">
            </div>
            <div class="col-md-3">
                <label>F.AR.COND: ( )</label>
                <input type="text" class="form-control form-control-checklist" name="f_ar_cond">
            </div>
        </div>

        <div class="row form-row">
            <div class="col-md-4">
                <label>OL/C:</label>
                <input type="text" class="form-control form-control-checklist" name="ol_c">
            </div>
            <div class="col-md-3">
                <label>QUANT:</label>
                <input type="text" class="form-control form-control-checklist" name="ol_c_quan">
            </div>
            <div class="col-md-3">
                <label>OL/H:</label>
                <input type="text" class="form-control form-control-checklist" name="ol_h">
            </div>
            <div class="col-md-2">
                <label>QUANT:</label>
                <input type="text" class="form-control form-control-checklist" name="ol_h_quan">
            </div>
        </div>

        <!-- VALORES -->
        <div class="row form-row" style="margin-top: 30px; border-top: 2px solid #dc3545; padding-top: 20px;">
            <div class="col-md-4">
                <label>VALOR PARCIAL:</label>
                <input type="text" class="form-control form-control-checklist moeda" id="valor_parcial" name="valor_parcial" placeholder="R$ 0,00" readonly>
            </div>
            <div class="col-md-4">
                <label>PARCELADO:</label>
                <input type="text" class="form-control form-control-checklist" id="parcelado" name="parcelado">
            </div>
        </div>

        <div class="row form-row">
            <div class="col-md-4">
                <label style="font-size: 16px; font-weight: bold;">VALOR TOTAL:</label>
                <input type="text" class="form-control form-control-checklist moeda" id="valor_total" name="valor_total" placeholder="R$ 0,00" readonly style="font-weight: bold; font-size: 16px;">
            </div>
            <div class="col-md-4">
                <label>PARCELADO:</label>
                <input type="text" class="form-control form-control-checklist" name="parcelado_total">
            </div>
            <div class="col-md-4 text-right">
                <button type="submit" class="btn btn-salvar">
                    <i class="fa fa-save"></i> SALVAR ORÇAMENTO
                </button>
            </div>
        </div>

    </form>
</div>

<script>
// Máscaras
$(document).ready(function() {
    $('.cpf').mask('000.000.000-00');
    $('.moeda').mask('#.##0,00', {reverse: true});
    $('.moeda-input').mask('#.##0,00', {reverse: true});

    // Busca CEP
    $('#cep').on('blur', function() {
        var cep = $(this).val().replace(/\D/g, '');
        if (cep.length === 8) {
            $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
                if (!data.erro) {
                    $('#rua').val(data.logradouro);
                    // Pode adicionar mais campos se necessário
                }
            });
        }
    });

    // Cálculo automático dos totais
    $('input.moeda-input').on('change', function() {
        calcularTotalLinha($(this).closest('tr'));
        calcularTotalGeral();
    });

    function calcularTotalLinha(linha) {
        var total = 0;
        linha.find('input.moeda-input').each(function() {
            var val = $(this).val().replace(/\D/g, '');
            val = val / 100;
            total += val;
        });
        linha.find('.total-item').val(total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    function calcularTotalGeral() {
        var totalGeral = 0;
        $('.total-item').each(function() {
            var val = $(this).val().replace(/\./g, '').replace(',', '.');
            totalGeral += parseFloat(val) || 0;
        });
        $('#valor_parcial').val(totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#valor_total').val(totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    // Submit do formulário
    $('#form-orcamento').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'api/salvar_orcamento.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert('Orçamento salvo com sucesso!');
                location.reload();
            },
            error: function() {
                alert('Erro ao salvar orçamento!');
            }
        });
    });
});
</script>
