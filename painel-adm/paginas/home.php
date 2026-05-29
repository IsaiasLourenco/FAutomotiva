<?php
// FAutomotiva/painel-adm/paginas/home.php
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

$total_orcamentos = $pdo->query("SELECT COUNT(*) FROM orcamentos WHERE DATE(data_criacao) = CURDATE()")->fetchColumn();
$total_aprovados = $pdo->query("SELECT COUNT(*) FROM orcamentos WHERE status = 'aprovado' AND DATE(data_criacao) = CURDATE()")->fetchColumn();
$total_vendas_hoje = $pdo->query("SELECT COALESCE(SUM(valor_total), 0) FROM orcamentos WHERE status = 'aprovado' AND DATE(data_criacao) = CURDATE()")->fetchColumn();
$pecas_cadastradas = $pdo->query("SELECT COUNT(*) FROM pecas WHERE ativo = 'Sim'")->fetchColumn();

$fornecedores = $pdo->query("SELECT id, nome FROM fornecedores WHERE ativo = 'Sim' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/orcamento_lista.css">

<div class="main-page">
    <div class="col_3">
        <div class="col-md-3 widget widget1">
            <div class="r3_counter_box"><i class="pull-left fa fa-file-text icon-rounded"></i>
                <div class="stats">
                    <h5><strong><?php echo $total_orcamentos; ?></strong></h5><span>Orçamentos Hoje</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 widget widget1">
            <div class="r3_counter_box"><i class="pull-left fa fa-check-circle user1 icon-rounded"></i>
                <div class="stats">
                    <h5><strong><?php echo $total_aprovados; ?></strong></h5><span>Aprovados</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 widget widget1">
            <div class="r3_counter_box"><i class="pull-left fa fa-money user2 icon-rounded"></i>
                <div class="stats">
                    <h5><strong>R$ <?php echo number_format($total_vendas_hoje, 2, ',', '.'); ?></strong></h5>
                    <span>Vendas Hoje</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 widget widget1">
            <div class="r3_counter_box"><i class="pull-left fa fa-cogs dollar1 icon-rounded"></i>
                <div class="stats">
                    <h5><strong><?php echo $pecas_cadastradas; ?></strong></h5><span>Peças Cadastradas</span>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row-one widgettable">
        <div class="col-md-12 content-top-2 card">
            <div class="agileinfo-cdr">
                <div class="card-header bg-success text-white">
                    <h3><i class="fa fa-calculator"></i> Novo Orçamento</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 position-relative"><label>Cliente *</label><input type="text"
                                class="form-control" id="busca_cliente" placeholder="Nome ou telefone..."
                                autocomplete="off"><input type="hidden" id="cliente_id">
                            <div id="autocomplete-clientes" class="autocomplete-list"></div>
                        </div>
                        <div class="col-md-3 position-relative"><label>Placa *</label><input type="text"
                                class="form-control" id="busca_placa" placeholder="ABC-1D23" maxlength="8"
                                style="text-transform:uppercase" autocomplete="off"><input type="hidden"
                                id="veiculo_id">
                            <div id="autocomplete-veiculos" class="autocomplete-list"></div>
                        </div>
                        <div class="col-md-2"><label>KM Atual</label><input type="number" class="form-control"
                                id="km_atual" placeholder="0"></div>
                        <div class="col-md-3"><label>Problema</label><input type="text" class="form-control"
                                id="problema" placeholder="Ex: barulho..."></div>
                    </div>
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-5 position-relative"><label>Peça *</label><input type="text"
                                class="form-control" id="busca_peca" placeholder="Ex: pastilha..." autocomplete="off">
                            <div id="autocomplete-pecas" class="autocomplete-list"></div><input type="hidden"
                                id="peca_id_selecionado">
                        </div>
                        <div class="col-md-2"><label>Qtd *</label><input type="number" class="form-control"
                                id="qtd_peca" value="1" min="1"></div>
                        <div class="col-md-3"><label>Obs</label><input type="text" class="form-control" id="obs_peca"
                                placeholder="Opcional"></div>
                        <div class="col-md-2"><button class="btn btn-success w-100" onclick="adicionarItem()"><i
                                    class="fa fa-plus"></i> Adicionar</button></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tabela-itens">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:20%;">Peça</th>
                                    <th style="width:6%;">Qtd</th>
                                    <th style="width:10%;">Obs</th><?php foreach ($fornecedores as $f): ?>
                                        <th><?php echo htmlspecialchars($f['nome']); ?></th><?php endforeach; ?>
                                    <th style="width:12%;">Melhor</th>
                                    <th style="width:8%;">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="lista-itens"></tbody>
                        </table>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4"><label>Mão de Obra (R$)</label><input type="text"
                                class="form-control moeda" id="mao_obra" value="0,00" onchange="recalcular()"></div>
                        <div class="col-md-8 text-end">
                            <p><strong>Custo:</strong> <span id="total_custo">R$ 0,00</span></p>
                            <p><strong>Venda:</strong> <span id="total_venda" class="text-success">R$ 0,00</span></p>
                            <p><strong>Total:</strong> <span id="total_final" class="text-primary h4">R$ 0,00</span></p>
                            <button class="btn btn-primary" onclick="salvarOrcamento()"><i class="fa fa-save"></i>
                                Salvar</button><button class="btn btn-outline-secondary" onclick="limparTudo()"><i
                                    class="fa fa-eraser"></i> Limpar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="listar" style="display:none;"></div>
<script>
    if (typeof pag === 'undefined' || pag === 'home') { window.listar = function () { return true; }; }

    // ✅ CORREÇÃO: Remove o .slice(0, -1) que estava removendo o último fornecedor!
    const FORNECEDORES = [<?php
    foreach ($fornecedores as $f) {
        echo "{id:" . (int) $f['id'] . ",nome:'" . addslashes($f['nome']) . "'},";
    }
    ?>]; // ← Sem slice!

    let itens = [];
    let pecaSelecionada = null;

    $('#busca_cliente').on('input', function () {
        const val = $(this).val().trim();
        if (val.length < 2) { $('#autocomplete-clientes').empty().hide(); return; }
        $.get('api/buscar_clientes.php', { q: val }, function (data) {
            const lista = $('#autocomplete-clientes').empty();
            if (!data.length) { lista.hide(); return; }
            data.forEach(c => {
                const item = $(`<div class="autocomplete-item"><strong>${c.nome}</strong><br><small>${c.telefone}</small></div>`);
                item.on('click', function () { $('#cliente_id').val(c.id); $('#busca_cliente').val(c.nome); lista.empty().hide(); });
                lista.append(item);
            });
            lista.show();
        }, 'json');
    });

    $('#busca_placa').on('input', function () {
        const val = $(this).val().trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (val.length < 3) { $('#autocomplete-veiculos').empty().hide(); return; }
        $.get('api/buscar_veiculos.php', { q: val }, function (data) {
            const lista = $('#autocomplete-veiculos').empty();
            if (!data.length) { lista.hide(); return; }
            data.forEach(v => {
                const item = $(`<div class="autocomplete-item"><strong>${v.placa}</strong> - ${v.modelo}</div>`);
                item.on('click', function () { $('#veiculo_id').val(v.id); $('#busca_placa').val(v.placa); $('#km_atual').val(v.km_atual || ''); lista.empty().hide(); });
                lista.append(item);
            });
            lista.show();
        }, 'json');
    });

    $('#busca_peca').on('input', function () {
        const val = $(this).val().trim();
        if (val.length < 2) { $('#autocomplete-pecas').empty().hide(); return; }
        $.get('api/buscar_pecas.php', { q: val }, function (data) {
            const lista = $('#autocomplete-pecas').empty();
            if (!data.length) { lista.hide(); return; }
            data.forEach(p => {
                const item = $(`<div class="autocomplete-item">${p.nome_padrao}</div>`);
                item.on('click', function () { pecaSelecionada = { id: p.id, nome: p.nome_padrao }; $('#busca_peca').val(p.nome_padrao); $('#peca_id_selecionado').val(p.id); lista.empty().hide(); });
                lista.append(item);
            });
            lista.show();
        }, 'json');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.autocomplete-list, #busca_cliente, #busca_placa, #busca_peca').length) {
            $('.autocomplete-list').empty().hide();
        }
    });

    function adicionarItem() {
        if (!pecaSelecionada) { alert('Selecione uma peça'); return; }
        itens.push({ id: pecaSelecionada.id, nome: pecaSelecionada.nome, qtd: parseInt($('#qtd_peca').val()) || 1, obs: $('#obs_peca').val(), precos: {} });
        pecaSelecionada = null;
        $('#busca_peca, #peca_id_selecionado, #obs_peca').val(''); $('#qtd_peca').val(1);
        renderizarTabela();
    }

    function renderizarTabela() {
        const tbody = $('#lista-itens').empty();
        itens.forEach((item, idx) => {
            let tr = `<tr><td><strong>${item.nome}</strong></td>`;
            tr += `<td><input type="number" class="form-control form-control-sm" value="${item.qtd}" min="1" onchange="itens[${idx}].qtd=parseInt(this.value)||1; recalcular()"></td>`;
            tr += `<td><input type="text" class="form-control form-control-sm" value="${item.obs}" onchange="itens[${idx}].obs=this.value"></td>`;
            FORNECEDORES.forEach(f => {
                const valor = item.precos[f.id] ?? 0;
                const valorFmt = valor > 0 ? valor.toFixed(2).replace('.', ',') : '0,00';
                tr += `<td><input type="text" class="form-control form-control-sm moeda" data-fornecedor-id="${f.id}" data-item-idx="${idx}" value="${valorFmt}" onchange="itens[${idx}].precos[${f.id}]=limparMoeda(this.value)||0; recalcular()"></td>`;
            });
            const precos = Object.values(item.precos).filter(p => p > 0);
            const menor = precos.length ? Math.min(...precos) : 0;
            const venda = menor > 0 ? calcularMarkup(menor) : 0;
            tr += `<td id="melhor_${idx}" class="text-center fw-bold" style="background:${venda > 0 ? '#d4edda' : 'transparent'}">${venda > 0 ? 'R$ ' + venda.toFixed(2).replace('.', ',') : '-'}</td>`;
            tr += `<td class="text-center"><button class="btn btn-sm btn-danger" onclick="removerItem(${idx})"><i class="fa fa-trash"></i></button></td></tr>`;
            tbody.append(tr);
        });
        $('.moeda').mask('#.##0,00', { reverse: true });
        recalcular();
    }

    function removerItem(idx) { itens.splice(idx, 1); renderizarTabela(); }

    function recalcular() {
        let totalCusto = 0;
        itens.forEach((item, idx) => {
            const precos = Object.values(item.precos).filter(p => p > 0);
            const menor = precos.length ? Math.min(...precos) : 0;
            const venda = menor > 0 ? calcularMarkup(menor) : 0;
            totalCusto += menor * item.qtd;
            const td = $(`#melhor_${idx}`);
            td.text(venda > 0 ? 'R$ ' + venda.toFixed(2).replace('.', ',') : '-');
        });
        const maoObra = limparMoeda($('#mao_obra').val()) || 0;
        const vendaPecas = calcularMarkup(totalCusto);
        const total = vendaPecas + maoObra;
        $('#total_custo').text('R$ ' + totalCusto.toFixed(2).replace('.', ','));
        $('#total_venda').text('R$ ' + vendaPecas.toFixed(2).replace('.', ','));
        $('#total_final').text('R$ ' + total.toFixed(2).replace('.', ','));
    }

    function calcularMarkup(v) { if (v <= 50) return v * 2; if (v <= 100) return v * 1.7; if (v <= 300) return v * 1.4; return v + 120; }
    function limparMoeda(val) { return parseFloat(val.replace('R$', '').replace(/\./g, '').replace(',', '.')) || 0; }

    function salvarOrcamento() {
        const cid = $('#cliente_id').val(), vid = $('#veiculo_id').val();
        if (!cid || !vid) { alert('Selecione cliente e veículo'); return; }
        if (!itens.length) { alert('Adicione pelo menos uma peça'); return; }
        $.ajax({
            url: 'api/salvar_orcamento.php', method: 'POST',
            data: { cliente_id: cid, veiculo_id: vid, km: $('#km_atual').val(), problema: $('#problema').val(), mao_obra: limparMoeda($('#mao_obra').val()), itens: JSON.stringify(itens.map(i => ({ peca_id: i.id, nome: i.nome, qtd: i.qtd, obs: i.obs, precos: i.precos }))) },
            dataType: 'json',
            success: function (res) { if (res.sucesso) { alert('✅ Orçamento #' + res.id + ' salvo!\nTotal: R$ ' + res.total); if (confirm('Gerar novo orçamento?')) limparTudo(); } else { alert('❌ Erro: ' + (res.erro || 'Desconhecido')); } },
            error: function () { alert('Erro de conexão'); }
        });
    }

    function limparTudo() {
        itens = []; pecaSelecionada = null;
        $('#busca_cliente, #cliente_id, #busca_placa, #veiculo_id, #km_atual, #problema').val('');
        $('#busca_peca, #peca_id_selecionado, #obs_peca').val('');
        $('#qtd_peca').val(1); $('#mao_obra').val('0,00');
        $('#lista-itens').empty(); recalcular();
    }

    $(document).ready(function () { $('.moeda').mask('#.##0,00', { reverse: true }); recalcular(); });
</script>
