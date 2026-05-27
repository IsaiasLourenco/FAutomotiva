<?php
// FAutomotiva/painel-adm/paginas/orcamentos.php
if (!isset($_SESSION['id_user'])) { header("Location: ../index.php"); exit; }

// Busca fornecedores ativos
$fornecedores = $pdo->query("SELECT id, nome FROM fornecedores WHERE ativo = 'Sim' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$num_fornecedores = count($fornecedores);
$largura_fornecedor = $num_fornecedores > 0 ? max(12, 55 / $num_fornecedores) : 15;
?>
<link rel="stylesheet" href="../css/orcamento.css">
<link rel="stylesheet" href="../css/style.css">

<style>
.checklist-container { background: #fff; padding: 15px; border: 2px solid #dc3545; max-width: 1400px; margin: 0 auto; font-size: 12px; }
.checklist-header { color: #dc3545; font-size: 24px; font-weight: bold; text-transform: uppercase; border-bottom: 3px solid #dc3545; padding: 10px; margin-bottom: 15px; text-align: center; }
.form-row { margin-bottom: 8px; }
.form-row label { font-weight: bold; color: #dc3545; font-size: 11px; text-transform: uppercase; margin-bottom: 2px; }
.form-control-checklist { border: 1px solid #dc3545; border-radius: 0; font-size: 12px; padding: 4px 8px; }
.form-control-checklist:focus { border-color: #dc3545; box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25); }
.table-checklist { border: 2px solid #dc3545; margin-top: 15px; font-size: 11px; }
.table-checklist th { background: #fff; color: #dc3545; border: 1px solid #dc3545; font-weight: bold; text-transform: uppercase; text-align: center; padding: 6px 4px; }
.table-checklist td { border: 1px solid #dc3545; padding: 3px; }
.table-checklist input { border: none; width: 100%; text-align: center; font-size: 11px; background: transparent; }
.table-checklist input:focus { outline: none; background: #fff3cd; }
.autocomplete-list { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ccc; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1050; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.autocomplete-item { padding: 6px 10px; cursor: pointer; border-bottom: 1px solid #eee; font-size: 12px; }
.autocomplete-item:hover { background: #007bff; color: #fff; }
.btn-salvar { background: #dc3545; color: #fff; border: none; padding: 10px 25px; font-weight: bold; text-transform: uppercase; margin-top: 15px; font-size: 12px; }
.btn-salvar:hover { background: #c82333; color: #fff; }
.total-item { font-weight: bold; background: #f8f9fa; }
</style>

<div class="checklist-container">
    <form id="form-orcamento" method="POST">

        <!-- HEADER -->
        <div class="checklist-header">
            CHECK-LIST &nbsp;&nbsp; <span style="font-size: 16px;">DATA: <input type="date" name="data_orcamento" style="border:none;background:transparent;font-weight:bold;color:#dc3545;"></span>
        </div>

        <!-- DADOS DO CLIENTE -->
        <div class="row form-row">
            <div class="col-md-6 position-relative">
                <label>NOME:</label>
                <input type="text" class="form-control form-control-checklist" id="cliente_nome" name="cliente_nome" placeholder="Busque ou digite..." required autocomplete="off">
                <input type="hidden" id="cliente_id" name="cliente_id">
                <div id="autocomplete-clientes" class="autocomplete-list"></div>
            </div>
            <div class="col-md-3">
                <label>CPF:</label>
                <input type="text" class="form-control form-control-checklist cpf" id="cliente_cpf" name="cliente_cpf" readonly>
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
            <div class="col-md-4 position-relative">
                <label>CARRO:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_busca" name="veiculo_busca" placeholder="Placa ou modelo..." autocomplete="off">
                <input type="hidden" id="veiculo_id" name="veiculo_id">
                <div id="autocomplete-veiculos" class="autocomplete-list"></div>
            </div>
            <div class="col-md-2">
                <label>PLACA:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_placa" name="veiculo_placa" maxlength="8" style="text-transform:uppercase" readonly>
            </div>
            <div class="col-md-2">
                <label>KM:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_km" name="veiculo_km">
            </div>
            <div class="col-md-2">
                <label>MOTOR:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_motor" name="veiculo_motor" readonly>
            </div>
            <div class="col-md-2">
                <label>ANO:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_ano" name="veiculo_ano" readonly>
            </div>
        </div>

        <div class="row form-row">
            <div class="col-md-3">
                <label>COR:</label>
                <input type="text" class="form-control form-control-checklist" id="veiculo_cor" name="veiculo_cor" readonly>
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
                    <option value="">NÃO</option><option value="D">D</option><option value="T">T</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>BALANCEAMENTO:</label>
                <select class="form-control form-control-checklist" name="balanceamento">
                    <option value="">NÃO</option><option value="D">D</option><option value="T">T</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>CAMB:</label>
                <select class="form-control form-control-checklist" name="camb">
                    <option value="">NÃO</option><option value="DD">DD</option><option value="DE">DE</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>ALINHAM TRZ:</label>
                <select class="form-control form-control-checklist" name="alinham_trz">
                    <option value="">NÃO</option><option value="D">D</option><option value="T">T</option>
                </select>
            </div>
        </div>

        <div class="row form-row">
            <div class="col-md-4"><label>MONTAGEM:</label><input type="text" class="form-control form-control-checklist" name="montagem"></div>
            <div class="col-md-4"><label>BICO:</label><input type="text" class="form-control form-control-checklist" name="bico"></div>
            <div class="col-md-4"><label>VL=:</label><input type="text" class="form-control form-control-checklist moeda" name="vl_bico" placeholder="R$ 0,00"></div>
        </div>

        <!-- PNEUS -->
        <div class="row form-row">
            <div class="col-md-4"><label>PNEUS - MARCA:</label><input type="text" class="form-control form-control-checklist" name="pneus_marca"></div>
            <div class="col-md-4"><label>QUANT:</label><input type="text" class="form-control form-control-checklist" name="pneus_quant"></div>
            <div class="col-md-4"><label>VL=:</label><input type="text" class="form-control form-control-checklist moeda" name="pneus_vl" placeholder="R$ 0,00"></div>
        </div>

        <!-- TABELA DE PEÇAS/SERVIÇOS -->
        <div class="table-responsive">
            <table class="table table-checklist">
                <thead>
                    <tr>
                        <th style="width:28%;">M.O / MEC</th>
                        <th style="width:7%;">SUSP DIAN</th>
                        <th style="width:7%;">SUSP TR</th>
                        <th style="width:7%;">M.O / S.P.</th>
                        <?php foreach ($fornecedores as $fornecedor): ?>
                            <th style="width:<?php echo $largura_fornecedor; ?>%;"><?php echo htmlspecialchars($fornecedor['nome']); ?></th>
                        <?php endforeach; ?>
                        <th style="width:9%;">TOTAL</th>
                    </tr>
                </thead>
                <tbody id="itens-orcamento">
                    <?php for ($i = 0; $i < 20; $i++): ?>
                    <tr>
                        <td><input type="text" name="mec[<?php echo $i; ?>]" placeholder="Peça/Serviço" class="busca-peca" data-index="<?php echo $i; ?>"></td>
                        <td>
                            <select name="susp_dian[<?php echo $i; ?>]" class="form-control form-control-sm">
                                <option value=""></option><option value="DD">DD</option><option value="DE">DE</option><option value="TD">TD</option><option value="TE">TE</option>
                            </select>
                        </td>
                        <td>
                            <select name="susp_tr[<?php echo $i; ?>]" class="form-control form-control-sm">
                                <option value=""></option><option value="DD">DD</option><option value="DE">DE</option><option value="TD">TD</option><option value="TE">TE</option>
                            </select>
                        </td>
                        <td><input type="text" name="mo_sp[<?php echo $i; ?>]"></td>
                        <?php foreach ($fornecedores as $fornecedor): ?>
                            <td><input type="text" name="fornecedor[<?php echo $fornecedor['id']; ?>][<?php echo $i; ?>]" class="moeda-input" placeholder="0,00"></td>
                        <?php endforeach; ?>
                        <td><input type="text" name="total[<?php echo $i; ?>]" class="total-item" readonly></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <!-- ÓLEO E FLUIDOS -->
        <div class="row form-row" style="margin-top:15px;">
            <div class="col-md-4"><label>OL/MOTOR:</label><input type="text" class="form-control form-control-checklist" name="ol_motor" placeholder="__W"></div>
            <div class="col-md-3"><label>QUAN:</label><input type="text" class="form-control form-control-checklist" name="ol_motor_quan" placeholder="__ / __"></div>
            <div class="col-md-3"><label>FIL ( ):</label><input type="text" class="form-control form-control-checklist" name="filtro"></div>
        </div>
        <div class="row form-row">
            <div class="col-md-3"><label>F.COMB: ( )</label><input type="text" class="form-control form-control-checklist" name="f_comb"></div>
            <div class="col-md-3"><label>F.AR: ( )</label><input type="text" class="form-control form-control-checklist" name="f_ar"></div>
            <div class="col-md-3"><label>F.AR.COND: ( )</label><input type="text" class="form-control form-control-checklist" name="f_ar_cond"></div>
        </div>
        <div class="row form-row">
            <div class="col-md-4"><label>OL/C:</label><input type="text" class="form-control form-control-checklist" name="ol_c"></div>
            <div class="col-md-3"><label>QUANT:</label><input type="text" class="form-control form-control-checklist" name="ol_c_quan"></div>
            <div class="col-md-3"><label>OL/H:</label><input type="text" class="form-control form-control-checklist" name="ol_h"></div>
            <div class="col-md-2"><label>QUANT:</label><input type="text" class="form-control form-control-checklist" name="ol_h_quan"></div>
        </div>

        <!-- VALORES -->
        <div class="row form-row" style="margin-top:20px; border-top:2px solid #dc3545; padding-top:15px;">
            <div class="col-md-4"><label>VALOR PARCIAL:</label><input type="text" class="form-control form-control-checklist moeda" id="valor_parcial" name="valor_parcial" placeholder="R$ 0,00" readonly></div>
            <div class="col-md-4"><label>PARCELADO:</label><input type="text" class="form-control form-control-checklist" id="parcelado" name="parcelado"></div>
        </div>
        <div class="row form-row">
            <div class="col-md-4">
                <label style="font-size:14px;font-weight:bold;">VALOR TOTAL:</label>
                <input type="text" class="form-control form-control-checklist moeda" id="valor_total" name="valor_total" placeholder="R$ 0,00" readonly style="font-weight:bold;font-size:14px;">
            </div>
            <div class="col-md-4"><label>PARCELADO:</label><input type="text" class="form-control form-control-checklist" name="parcelado_total"></div>
            <div class="col-md-4 text-right">
                <button type="submit" class="btn btn-salvar"><i class="fa fa-save"></i> SALVAR</button>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Máscaras
    $('.cpf').mask('000.000.000-00');
    $('.moeda, .moeda-input').mask('#.##0,00', {reverse: true});

    // ===== BUSCA CLIENTE =====
    $('#cliente_nome').on('input', function() {
        const val = $(this).val().trim();
        if (val.length < 2) { $('#autocomplete-clientes').empty().hide(); return; }
        $.get('../api/buscar_clientes.php', { q: val }, function(data) {
            const lista = $('#autocomplete-clientes').empty();
            if (data.length === 0) { lista.hide(); return; }
            data.forEach(c => {
                const item = $(`<div class="autocomplete-item"><strong>${c.nome}</strong><br><small>${c.telefone} • CPF: ${c.cpf||'-'}</small></div>`);
                item.on('click', function() {
                    $('#cliente_id').val(c.id);
                    $('#cliente_nome').val(c.nome);
                    $('#cliente_cpf').val(c.cpf||'');
                    $('#cliente_cel').val(c.telefone||'');
                    $('#cep').val(c.cep||''); $('#rua').val(c.rua||''); $('#numero').val(c.numero||'');
                    $('#bairro').val(c.bairro||''); $('#cidade').val(c.cidade||''); $('#estado').val(c.estado||'');
                    lista.empty().hide();
                    // Busca veículos deste cliente
                    buscarVeiculosPorCliente(c.id);
                });
                lista.append(item);
            });
            lista.show();
        }, 'json');
    });

    // ===== BUSCA VEÍCULO =====
    function buscarVeiculosPorCliente(cliente_id) {
        if (!cliente_id) return;
        $.get('../api/buscar_veiculos.php', { cliente_id: cliente_id }, function(data) {
            const lista = $('#autocomplete-veiculos').empty();
            data.forEach(v => {
                const item = $(`<div class="autocomplete-item"><strong>${v.placa}</strong> - ${v.modelo}<br><small>${v.marca||''} ${v.motor||''} ${v.ano||''}</small></div>`);
                item.on('click', function() {
                    $('#veiculo_id').val(v.id);
                    $('#veiculo_busca').val(v.placa + ' - ' + v.modelo);
                    $('#veiculo_placa').val(v.placa||''); $('#veiculo_km').val(v.km_atual||'');
                    $('#veiculo_motor').val(v.motor||''); $('#veiculo_ano').val(v.ano||'');
                    $('#veiculo_cor').val(v.cor||'');
                    lista.empty().hide();
                });
                lista.append(item);
            });
            if (data.length > 0) lista.show(); else lista.hide();
        }, 'json');
    }

    $('#veiculo_busca').on('input', function() {
        const val = $(this).val().trim().toUpperCase();
        const cid = $('#cliente_id').val();
        if (val.length < 3 && !cid) { $('#autocomplete-veiculos').empty().hide(); return; }
        $.get('../api/buscar_veiculos.php', { q: val, cliente_id: cid }, function(data) {
            const lista = $('#autocomplete-veiculos').empty();
            if (data.length === 0) { lista.hide(); return; }
            data.forEach(v => {
                const item = $(`<div class="autocomplete-item"><strong>${v.placa}</strong> - ${v.modelo}<br><small>${v.marca||''} • ${v.cliente_nome||''}</small></div>`);
                item.on('click', function() {
                    $('#veiculo_id').val(v.id);
                    $('#veiculo_busca').val(v.placa + ' - ' + v.modelo);
                    $('#veiculo_placa').val(v.placa||''); $('#veiculo_km').val(v.km_atual||'');
                    $('#veiculo_motor').val(v.motor||''); $('#veiculo_ano').val(v.ano||'');
                    $('#veiculo_cor').val(v.cor||'');
                    lista.empty().hide();
                });
                lista.append(item);
            });
            lista.show();
        }, 'json');
    });

    // ===== BUSCA PEÇA (autocomplete na tabela) =====
    $(document).on('input', '.busca-peca', function() {
        const input = $(this);
        const val = input.val().trim();
        const idx = input.data('index');
        if (val.length < 2) { $(`#autocomplete-peca-${idx}`).remove(); return; }

        $.get('../api/buscar_pecas.php', { q: val }, function(data) {
            let lista = $(`#autocomplete-peca-${idx}`);
            if (!lista.length) {
                lista = $(`<div id="autocomplete-peca-${idx}" class="autocomplete-list"></div>`);
                input.after(lista);
            } else { lista.empty(); }
            if (data.length === 0) { lista.hide(); return; }
            data.forEach(p => {
                const item = $(`<div class="autocomplete-item">${p.nome_padrao} ${p.marca_recomendada?'• '+p.marca_recomendada:''}</div>`);
                item.on('click', function() {
                    input.val(p.nome_padrao);
                    lista.remove();
                });
                lista.append(item);
            });
            lista.show();
        }, 'json');
    });

    // Fecha autocompletes ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.autocomplete-list, #cliente_nome, #veiculo_busca, .busca-peca').length) {
            $('.autocomplete-list').empty().hide();
        }
    });

    // ===== CEP =====
    $('#cep').on('blur', function() {
        const cep = $(this).val().replace(/\D/g, '');
        if (cep.length === 8) {
            $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
                if (!data.erro) { $('#rua').val(data.logradouro); }
            });
        }
    });

    // ===== CÁLCULOS =====
    $('input.moeda-input').on('change keyup', function() {
        calcularTotalLinha($(this).closest('tr'));
        calcularTotalGeral();
    });

    function calcularTotalLinha(linha) {
        let total = 0;
        linha.find('input.moeda-input').each(function() {
            const val = parseFloat($(this).val().replace(/\D/g, '')) / 100 || 0;
            total += val;
        });
        linha.find('.total-item').val(total.toLocaleString('pt-BR', {minimumFractionDigits:2}));
    }

    function calcularTotalGeral() {
        let total = 0;
        $('.total-item').each(function() {
            total += parseFloat($(this).val().replace(/\./g,'').replace(',','.')) || 0;
        });
        $('#valor_parcial, #valor_total').val(total.toLocaleString('pt-BR', {minimumFractionDigits:2}));
    }

    // ===== SALVAR =====
    $('#form-orcamento').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: '../api/salvar_orcamento.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                try {
                    const r = JSON.parse(res);
                    if (r.sucesso) { alert('✅ Orçamento #' + r.id + ' salvo!'); location.reload(); }
                    else { alert('❌ ' + (r.erro||'Erro desconhecido')); }
                } catch(err) { alert('✅ Salvo!'); location.reload(); }
            },
            error: function() { alert('Erro de conexão!'); }
        });
    });
});
</script>
