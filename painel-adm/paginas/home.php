<?php
// FAutomotiva/painel-adm/paginas/home.php
// Página principal do sistema: Orçamento Rápido

// Dados para os cards (consultas reais do banco)
$total_orcamentos = $pdo->query("SELECT COUNT(*) FROM orcamentos WHERE DATE(data_criacao) = CURDATE()")->fetchColumn();
$total_aprovados = $pdo->query("SELECT COUNT(*) FROM orcamentos WHERE status = 'aprovado' AND DATE(data_criacao) = CURDATE()")->fetchColumn();
$total_vendas_hoje = $pdo->query("SELECT COALESCE(SUM(valor_total), 0) FROM orcamentos WHERE status = 'aprovado' AND DATE(data_criacao) = CURDATE()")->fetchColumn();
$pecas_cadastradas = $pdo->query("SELECT COUNT(*) FROM pecas WHERE ativo = 1")->fetchColumn();
?>
<link rel="stylesheet" href="../css/style.css">

<div class="main-page">

    <!-- CARDS DE RESUMO -->
    <div class="col_3">
        <div class="col-md-3 widget widget1">
            <div class="r3_counter_box">
                <i class="pull-left fa fa-file-text icon-rounded"></i>
                <div class="stats">
                    <h5><strong><?php echo $total_orcamentos; ?></strong></h5>
                    <span>Orçamentos Hoje</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 widget widget1">
            <div class="r3_counter_box">
                <i class="pull-left fa fa-check-circle user1 icon-rounded"></i>
                <div class="stats">
                    <h5><strong><?php echo $total_aprovados; ?></strong></h5>
                    <span>Aprovados</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 widget widget1">
            <div class="r3_counter_box">
                <i class="pull-left fa fa-money user2 icon-rounded"></i>
                <div class="stats">
                    <h5><strong>R$ <?php echo number_format($total_vendas_hoje, 2, ',', '.'); ?></strong></h5>
                    <span>Vendas Hoje</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 widget widget1">
            <div class="r3_counter_box">
                <i class="pull-left fa fa-cogs dollar1 icon-rounded"></i>
                <div class="stats">
                    <h5><strong><?php echo $pecas_cadastradas; ?></strong></h5>
                    <span>Peças Cadastradas</span>
                </div>
            </div>
        </div>
        <div class="clearfix"> </div>
    </div>

    <!-- MÓDULO DE ORÇAMENTO (conteúdo principal) -->
    <div class="row-one widgettable">
        <div class="col-md-12 content-top-2 card">
            <div class="agileinfo-cdr">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fa fa-calculator"></i> Novo Orçamento</h3>
                </div>

                <div class="card-body">

                    <!-- DADOS DO CLIENTE/VEÍCULO -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Cliente *</label>
                            <input type="text" class="form-control" id="busca_cliente" placeholder="Nome ou telefone...">
                            <input type="hidden" id="cliente_id">
                        </div>
                        <div class="col-md-3">
                            <label>Placa *</label>
                            <input type="text" class="form-control" id="busca_placa" placeholder="ABC-1D23" maxlength="8" style="text-transform:uppercase">
                            <input type="hidden" id="veiculo_id">
                        </div>
                        <div class="col-md-2">
                            <label>KM Atual</label>
                            <input type="number" class="form-control" id="km_atual" placeholder="0">
                        </div>
                        <div class="col-md-3">
                            <label>Problema Relatado</label>
                            <input type="text" class="form-control" id="problema" placeholder="Ex: barulho na suspensão">
                        </div>
                    </div>

                    <!-- ADICIONAR PEÇAS -->
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-5 position-relative">
                            <label>Peça (busque por nome ou apelido) *</label>
                            <input type="text" class="form-control" id="busca_peca" placeholder="Ex: panela, tambor, bomba...">
                            <div id="autocomplete-pecas" class="autocomplete-list"></div>
                            <input type="hidden" id="peca_id_selecionado">
                        </div>
                        <div class="col-md-2">
                            <label>Qtd *</label>
                            <input type="number" class="form-control" id="qtd_peca" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label>Observação</label>
                            <input type="text" class="form-control" id="obs_peca" placeholder="Opcional">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-success w-100" onclick="adicionarItem()">
                                <i class="fa fa-plus"></i> Adicionar
                            </button>
                        </div>
                    </div>

                    <!-- TABELA DE ITENS -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tabela-itens">
                            <thead class="table-light">
                                <tr>
                                    <th>Peça</th><th>Qtd</th><th>Obs</th>
                                    <th>Loja A</th><th>Loja B</th><th>Loja C</th>
                                    <th>Loja D</th><th>Loja E</th>
                                    <th>Melhor</th><th>Ação</th>
                                </tr>
                            </thead>
                            <tbody id="lista-itens"></tbody>
                        </table>
                    </div>

                    <!-- TOTAIS -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label>Mão de Obra (R$)</label>
                            <input type="text" class="form-control moeda" id="mao_obra" value="0,00" onchange="recalcular()">
                        </div>
                        <div class="col-md-8 text-end">
                            <p><strong>Custo Peças:</strong> <span id="total_custo">R$ 0,00</span></p>
                            <p><strong>Venda Peças:</strong> <span id="total_venda" class="text-success">R$ 0,00</span></p>
                            <p><strong>Total ao Cliente:</strong> <span id="total_final" class="text-primary h4">R$ 0,00</span></p>
                            <button class="btn btn-primary" onclick="salvarOrcamento()">
                                <i class="fa fa-save"></i> Salvar Orçamento
                            </button>
                            <button class="btn btn-outline-secondary" onclick="limparTudo()">
                                <i class="fa fa-eraser"></i> Limpar
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<!-- CSS do autocomplete -->
<style>
.autocomplete-list {
    position: absolute; top: 100%; left: 0; right: 0;
    background: #fff; border: 1px solid #ccc; border-radius: 4px;
    max-height: 200px; overflow-y: auto; z-index: 1050;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.autocomplete-item {
    padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee;
}
.autocomplete-item:hover { background: #007bff; color: #fff; }
.highlight-melhor { background: #d4edda !important; font-weight: 600; }
</style>

<!-- JS do módulo de orçamento -->
<script>
// ✅ Fornecedores dinâmicos do banco (passados via PHP)
const FORNECEDORES = [<?php
    foreach ($fornecedores as $f) {
        echo "'" . addslashes($f['nome']) . "',";
    }
?>].slice(0, -1); // Remove última vírgula
let itens = [];
let pecaSelecionada = null;
let clienteSelecionado = null;
let veiculoSelecionado = null;

// ===== AUTOCOMPLETE: CLIENTES =====
$('#busca_cliente').on('input', function() {
    const val = $(this).val().trim();
    if (val.length < 2) { $('#autocomplete-clientes').remove(); return; }
    $.get('api/buscar_clientes.php', { q: val }, function(data) {
        let lista = $('#autocomplete-clientes');
        if (!lista.length) {
            lista = $('<div id="autocomplete-clientes" class="autocomplete-list"></div>');
            $('#busca_cliente').after(lista);
        } else { lista.empty(); }
        data.forEach(c => {
            const item = $(`<div class="autocomplete-item">${c.nome} • ${c.telefone}</div>`);
            item.on('click', function() {
                clienteSelecionado = { id: c.id, nome: c.nome };
                $('#busca_cliente').val(c.nome);
                $('#cliente_id').val(c.id);
                lista.remove();
            });
            lista.append(item);
        });
    }, 'json');
});

// ===== AUTOCOMPLETE: VEÍCULOS =====
$('#busca_placa').on('input', function() {
    const val = $(this).val().trim().toUpperCase();
    if (val.length < 3) { $('#autocomplete-veiculos').remove(); return; }
    $.get('api/buscar_veiculos.php', { q: val }, function(data) {
        let lista = $('#autocomplete-veiculos');
        if (!lista.length) {
            lista = $('<div id="autocomplete-veiculos" class="autocomplete-list"></div>');
            $('#busca_placa').after(lista);
        } else { lista.empty(); }
        data.forEach(v => {
            const item = $(`<div class="autocomplete-item">${v.placa} • ${v.modelo}</div>`);
            item.on('click', function() {
                veiculoSelecionado = { id: v.id, placa: v.placa };
                $('#busca_placa').val(v.placa);
                $('#veiculo_id').val(v.id);
                lista.remove();
            });
            lista.append(item);
        });
    }, 'json');
});

// ===== AUTOCOMPLETE: PEÇAS =====
$('#busca_peca').on('input', function() {
    const val = $(this).val().trim();
    if (val.length < 2) { $('#autocomplete-pecas').empty(); return; }
    $.get('api/buscar_pecas.php', { q: val }, function(data) {
        const lista = $('#autocomplete-pecas').empty();
        data.forEach(p => {
            const item = $(`<div class="autocomplete-item">${p.nome_padrao}</div>`);
            item.on('click', function() {
                pecaSelecionada = { id: p.id, nome: p.nome_padrao };
                $('#busca_peca').val(p.nome_padrao);
                $('#peca_id_selecionado').val(p.id);
                lista.empty();
            });
            lista.append(item);
        });
    }, 'json');
});

// Fecha autocomplete ao clicar fora
$(document).on('click', function(e) {
    if (!$(e.target).closest('.autocomplete-list, #busca_cliente, #busca_placa, #busca_peca').length) {
        $('.autocomplete-list').remove();
    }
});

// ===== FUNÇÕES DO ORÇAMENTO =====
function adicionarItem() {
    if (!pecaSelecionada) { alert('Selecione uma peça na lista'); return; }
    itens.push({
        id: pecaSelecionada.id,
        nome: pecaSelecionada.nome,
        qtd: parseInt($('#qtd_peca').val()) || 1,
        obs: $('#obs_peca').val(),
        precos: FORNECEDORES.map(() => 0)
    });
    pecaSelecionada = null;
    $('#busca_peca, #peca_id_selecionado, #obs_peca').val('');
    $('#qtd_peca').val(1);
    renderizarTabela();
}

function renderizarTabela() {
    const tbody = $('#lista-itens').empty();
    itens.forEach((item, idx) => {
        let tr = `<tr>
            <td><strong>${item.nome}</strong></td>
            <td><input type="number" class="form-control form-control-sm" value="${item.qtd}" min="1" onchange="itens[${idx}].qtd=parseInt(this.value)||1; recalcular()"></td>
            <td><input type="text" class="form-control form-control-sm" value="${item.obs}" onchange="itens[${idx}].obs=this.value"></td>`;
            <?php foreach ($fornecedores as $fornecedor): ?>
                <th style="width:<?php echo $largura_fornecedor; ?>%;"><?php echo htmlspecialchars($fornecedor['nome']); ?></th>
            <?php endforeach; ?>
        tr += `<td id="melhor_${idx}" class="text-center fw-bold">-</td>
            <td class="text-center"><button class="btn btn-sm btn-danger" onclick="removerItem(${idx})"><i class="fa fa-trash"></i></button></td>
        </tr>`;
        tbody.append(tr);
    });
    recalcular();
}

function removerItem(idx) { itens.splice(idx, 1); renderizarTabela(); }

function recalcular() {
    let totalCusto = 0;
    itens.forEach((item, idx) => {
        const precosValidos = item.precos.filter(p => p > 0);
        const menor = precosValidos.length ? Math.min(...precosValidos) : 0;
        const venda = menor > 0 ? calcularMarkup(menor) : 0;
        totalCusto += menor * item.qtd;
        const td = $(`#melhor_${idx}`);
        td.text(venda > 0 ? 'R$ ' + venda.toFixed(2).replace('.',',') : '-');
        td.toggleClass('highlight-melhor', venda > 0);
    });
    const maoObra = limparMoeda($('#mao_obra').val()) || 0;
    const vendaPecas = calcularMarkup(totalCusto);
    const total = vendaPecas + maoObra;
    $('#total_custo').text('R$ ' + totalCusto.toFixed(2).replace('.',','));
    $('#total_venda').text('R$ ' + vendaPecas.toFixed(2).replace('.',','));
    $('#total_final').text('R$ ' + total.toFixed(2).replace('.',','));
}

function calcularMarkup(valor) {
    if (valor <= 50) return valor * 2;
    if (valor <= 100) return valor * 1.7;
    if (valor <= 300) return valor * 1.4;
    return valor + 120;
}

function limparMoeda(val) {
    return parseFloat(val.replace('R$','').replace(/\./g,'').replace(',','.').trim()) || 0;
}

function salvarOrcamento() {
    const cid = $('#cliente_id').val(), vid = $('#veiculo_id').val();
    if (!cid || !vid) { alert('Selecione cliente e veículo'); return; }
    if (!itens.length) { alert('Adicione pelo menos uma peça'); return; }

    $.ajax({
        url: 'api/salvar_orcamento.php',
        method: 'POST',
        data: {
            cliente_id: cid, veiculo_id: vid,
            km: $('#km_atual').val(), problema: $('#problema').val(),
            mao_obra: limparMoeda($('#mao_obra').val()),
            itens: JSON.stringify(itens)
        },
        dataType: 'json',
        success: function(res) {
            if (res.sucesso) {
                alert('✅ Orçamento #' + res.id + ' salvo!\nTotal: R$ ' + res.total);
                if (confirm('Gerar novo orçamento?')) { limparTudo(); }
            } else {
                alert('❌ Erro: ' + (res.erro || 'Desconhecido'));
            }
        },
        error: function() { alert('Erro de comunicação com o servidor'); }
    });
}

function limparTudo() {
    itens = [];
    pecaSelecionada = clienteSelecionado = veiculoSelecionado = null;
    $('#busca_cliente, #cliente_id, #busca_placa, #veiculo_id, #km_atual, #problema').val('');
    $('#busca_peca, #peca_id_selecionado, #obs_peca').val('');
    $('#qtd_peca').val(1);
    $('#mao_obra').val('0,00');
    $('#lista-itens').empty();
    recalcular();
}

// Inicializa
$(document).ready(function() { recalcular(); });
</script>
