<?php
$tabela = 'receber';
require_once("../../../conexao.php");

$data_atual = date('Y-m-d');
$id = @$_POST['id'] ?? 0;

// ✅ Inicializa totais
$total_pago = 0;
$total_pendentes = 0;
$total_vencidas = 0;

// ✅ Query preparada + IS NULL + ordenação
$query = $pdo->prepare("SELECT * FROM {$tabela} WHERE paciente = :id ORDER BY data_vencimento ASC");
$query->execute([':id' => $id]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = count($res);

if ($linhas > 0) {
    echo '<table class="table table-bordered table-sm tabela-pequena" id="tabela-debitos">';
    echo '<thead class="bg-light"><tr>';
    echo '<th class="esc">Descrição</th>';
    echo '<th class="text-right">Valor</th>';
    echo '<th class="text-center">Vencimento</th>';
    echo '<th class="text-center esc">Status</th>';
    echo '<th class="text-center">Ação</th>';
    echo '</tr></thead><tbody>';

    foreach ($res as $r) {
        $id_conta           = $r['id'];
        $descricao          = htmlspecialchars($r['descricao']);
        $valor              = $r['valor'] ?? 0;
        $subtotal           = $r['subtotal'] ?? $valor;
        $data_vencimento    = $r['data_vencimento'];
        $data_pagamento     = $r['data_pagamento'];
        $forma_pagamento_nome = htmlspecialchars($r['forma_pagamento_nome'] ?? '');

        // ✅ Formatações para exibição
        $data_vencimentoF = !empty($data_vencimento) && $data_vencimento != '0000-00-00'
            ? date('d/m/Y', strtotime($data_vencimento))
            : '-';
        $valorF = 'R$ ' . number_format($subtotal, 2, ',', '.');

        // ✅ Formatações para passar no onclick (JavaScript)
        $valor_js = 'R$ ' . number_format($subtotal, 2, ',', '.');
        $descricao_js = addslashes($descricao);
        $forma_pgto_js = addslashes($forma_pagamento_nome);
        $data_vencimento_iso = !empty($data_vencimento) && $data_vencimento != '0000-00-00'
            ? date('Y-m-d', strtotime($data_vencimento))
            : '';

        // ✅ Classificação: Pago / Vencida / Pendente
        $status_class = '';
        $status_text = '';
        $mostrar_botao = false;

        if (!empty($data_pagamento) && $data_pagamento != '0000-00-00') {
            $status_class = 'badge badge-success';
            $status_text = 'Pago';
            $total_pago += $subtotal;
        } elseif (!empty($data_vencimento) && $data_vencimento != '0000-00-00' && strtotime($data_vencimento) < strtotime($data_atual)) {
            $status_class = 'badge badge-danger';
            $status_text = 'Vencida';
            $total_vencidas += $subtotal;
            $mostrar_botao = true;
        } else {
            $status_class = 'badge badge-warning';
            $status_text = 'Pendente';
            $total_pendentes += $subtotal;
            $mostrar_botao = true;
        }

        // ✅ Botão de baixar (só aparece se não pago)
        $botao_html = '';
        if ($mostrar_botao) {
            // ✅ Nome único para evitar conflito com ajax.js
            $botao_html = '<a href="#" onclick="abrirModalBaixaPaciente(\'' . $id_conta . '\', \'' . $valor_js . '\', \'' . $descricao_js . '\', \'' . $forma_pgto_js . '\', \'' .
                $data_vencimento_iso . '\'); return false;"
                                title="Baixar Conta">
                                <i class="fa fa-check-square text-success" style="font-size:16px"></i>
                            </a>';
        } else {
            $botao_html = '<i class="fa fa-check-circle text-muted" style="font-size:16px" title="Já baixado"></i>';
        }

        // ✅ Linha da tabela (mantendo classes originais)
        echo '<tr>';
        echo '<td class="align-middle esc" style="font-size:10px">' . $descricao . '</td>';
        echo '<td class="text-right font-weight-bold align-middle" style="font-size:10px">' . $valorF . '</td>';
        echo '<td class="text-center align-middle" style="font-size:10px">' . $data_vencimentoF . '</td>';
        echo '<td class="text-center align-middle esc" style="font-size:10px"><span class="' . $status_class . '">' . $status_text . '</span></td>';
        echo '<td class="text-center align-middle" style="font-size:10px">' . $botao_html . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    // ✅ Formata totais para exibição
    $total_pagoF = number_format($total_pago, 2, ',', '.');
    $total_pendentesF = number_format($total_pendentes, 2, ',', '.');
    $total_vencidasF = number_format($total_vencidas, 2, ',', '.');

    // ✅ Resumo no mesmo estilo da Modal Dados (mantendo classe "negrito")
    echo '<div class="row bg-light p-2 rounded mt-2" style="font-size:10px">';
    echo '<div class="col-md-4 text-center negrito">
                    <small class="text-muted">Total Pago</small><br>
                    <span class="font-weight-bold text-success">R$ ' . $total_pagoF . '</span>
              </div>';
    echo '<div class="col-md-4 text-center negrito">
                    <small class="text-muted">Total Pendente</small><br>
                    <span class="font-weight-bold text-warning">R$ ' . $total_pendentesF . '</span>
              </div>';
    echo '<div class="col-md-4 text-center negrito">
                    <small class="text-muted">Total Vencida</small><br>
                    <span class="font-weight-bold text-danger">R$ ' . $total_vencidasF . '</span>
              </div>';
    echo '</div>';
} else {
    echo '<div class="alert alert-info mt-3" style="font-size:10px">Nenhuma conta encontrada para este paciente.</div>';
}
?>

<!-- ✅ JAVASCRIPT COM AS DUAS FUNCIONALIDADES -->
<script type="text/javascript">
    // ✅ FUNÇÃO 1: Abrir modal de baixa (REDIRECIONA - volta ao que funcionava)
    function abrirModalBaixaPaciente(id, valor, descricao, forma_pgto, data_vencimento) {
        // ✅ Adiciona parâmetro 'voltar' para saber de onde veio
        var url = 'index.php?pagina=receber&baixar_id=' + id +
            '&baixar_valor=' + encodeURIComponent(valor) +
            '&baixar_desc=' + encodeURIComponent(descricao) +
            '&voltar=clientes'; // ← NOVO: indica a origem
        window.location.href = url;
    }

    // ✅ FUNÇÃO 2: Filtro de status (funciona independente)
    $(document).ready(function() {
        $('#filtro_status_contas').on('change', function() {
            var filtro = $(this).val();
            if (filtro === 'todas') {
                $('#tabela-debitos tbody tr').show();
            } else {
                $('#tabela-debitos tbody tr').hide();
                if (filtro === 'pendentes') {
                    $('#tabela-debitos tbody tr').has('.badge-warning').show();
                } else if (filtro === 'vencidas') {
                    $('#tabela-debitos tbody tr').has('.badge-danger').show();
                } else if (filtro === 'pagas') {
                    $('#tabela-debitos tbody tr').has('.badge-success').show();
                }
            }
        });
    });
</script>
