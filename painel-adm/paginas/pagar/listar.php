<?php
$tabela = 'receber';
require_once("../../../conexao.php");

$query = $pdo->query("SELECT * FROM $tabela ORDER BY id DESC");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);

if ($linhas > 0) {
    echo '<table class="table table-hover tabela-pequena" id="tabela">
        <thead> 
            <tr> 
                <th>Descrição</th>	
                <th class="esc">Paciente</th>	    
                <th class="esc">Data Lançamento</th>	
                <th class="esc">Data Pagamento</th>	
                <th class="esc">Valor</th>	
                <th class="esc">Arquivo</th>	
                <th>Ações</th>
            </tr> 
        </thead> 
        <tbody>';

    for ($i = 0; $i < $linhas; $i++) {
        $id                 = $res[$i]['id'];
        $descricao          = $res[$i]['descricao'];
        $paciente_id        = $res[$i]['paciente'];
        $valor              = $res[$i]['valor'];
        $data_vencimento    = $res[$i]['data_vencimento'];
        $data_lancamento    = $res[$i]['data_lancamento'];
        $data_pagamento     = $res[$i]['data_pagamento'];
        $forma_pagamento_id = $res[$i]['forma_pagamento'];
        $frequencia_id      = $res[$i]['frequencia'];
        $obs                = $res[$i]['obs'];
        $arquivo            = $res[$i]['arquivo'];

        $valorF = 'R$ ' . number_format($valor, 2, ',', '.');
        $data_vencimentoF = (!empty($data_vencimento) && $data_vencimento != '0000-00-00') ? date('d/m/Y', strtotime($data_vencimento)) : '-';
        $data_lancamentoF = (!empty($data_lancamento) && $data_lancamento != '0000-00-00') ? date('d/m/Y', strtotime($data_lancamento)) : '-';
        $data_pagamentoF_tabela = (!empty($data_pagamento) && $data_pagamento != '0000-00-00') ? date('d/m/Y', strtotime($data_pagamento)) : '<span class="text-muted">Não pago</span>';
        $data_pagamentoF_js = (!empty($data_pagamento) && $data_pagamento != '0000-00-00') ? date('d/m/Y', strtotime($data_pagamento)) : 'Não pago';
        $data_vencimento_iso = (!empty($data_vencimento) && $data_vencimento != '0000-00-00') ? date('Y-m-d', strtotime($data_vencimento)) : '';
        $data_pagamento_iso = (!empty($data_pagamento) && $data_pagamento != '0000-00-00') ? date('Y-m-d', strtotime($data_pagamento)) : '';

        // Busca nomes relacionados
        $qp = $pdo->prepare("SELECT nome FROM pacientes WHERE id = :id LIMIT 1");
        $qp->execute([":id" => $paciente_id]);
        $paciente_nome = ($rp = $qp->fetch(PDO::FETCH_ASSOC)) ? $rp['nome'] : 'Paciente Desconhecido';

        $qf = $pdo->prepare("SELECT frequencia FROM frequencias WHERE id = :id LIMIT 1");
        $qf->execute([":id" => $frequencia_id]);
        $frequencia_nome = ($rf = $qf->fetch(PDO::FETCH_ASSOC)) ? $rf['frequencia'] : 'Frequência Desconhecida';

        $qfp = $pdo->prepare("SELECT nome FROM forma_pagamento WHERE id = :id LIMIT 1");
        $qfp->execute([":id" => $forma_pagamento_id]);
        $forma_pagamento_nome = ($rfp = $qfp->fetch(PDO::FETCH_ASSOC)) ? $rfp['nome'] : 'Forma de Pagamento Desconhecida';

        // ✅ Usa json_encode para escapar valores para JavaScript (mais seguro)
        $j = [
            'descricao' => json_encode($descricao),
            'paciente_nome' => json_encode($paciente_nome),
            'valorF' => json_encode($valorF),
            'data_vencimentoF' => json_encode($data_vencimentoF),
            'data_lancamentoF' => json_encode($data_lancamentoF),
            'data_pagamentoF_js' => json_encode($data_pagamentoF_js),
            'data_vencimento_iso' => json_encode($data_vencimento_iso),
            'data_pagamento_iso' => json_encode($data_pagamento_iso),
            'forma_pagamento_nome' => json_encode($forma_pagamento_nome),
            'frequencia_nome' => json_encode($frequencia_nome),
            'obs' => json_encode($obs),
            'arquivo' => json_encode($arquivo),
            'multa' => json_encode($res[$i]['multa']),
            'juros' => json_encode($res[$i]['juros']),
            'desconto' => json_encode($res[$i]['desconto']),
            'subtotal' => json_encode($res[$i]['subtotal']),
            'referencia' => json_encode($res[$i]['referencia']),
            'id_referencia' => json_encode($res[$i]['id_referencia']),
        ];

        // ✅ Output da linha (onclicks em LINHA ÚNICA para evitar quebras)
        echo '<tr>
            <td><input type="checkbox" id="seletor-' . $id . '" class="form-check-input" onchange="selecionar(\'' . $id . '\')"> ' . $descricao . '</td>
            <td class="esc">' . $paciente_nome . '</td>
            <td class="esc">' . $data_lancamentoF . '</td>
            <td class="esc">' . $data_pagamentoF_tabela . '</td>
            <td class="esc">' . $valorF . '</td>
            <td class="esc"><a href="images/receber/' . $arquivo . '" target="_blank"><img src="images/receber/' . $arquivo . '" width="25px"></a></td>
            <td>
                <a href="#" onclick="editar(' . $id . ', ' . $j['descricao'] . ', ' . $paciente_id . ', ' . $j['valorF'] . ', ' . $j['data_vencimento_iso'] . ', ' . $j['data_lancamentoF'] . ', ' . $j['data_pagamento_iso'] . ', ' . $forma_pagamento_id . ', ' . $frequencia_id . ', ' . $j['obs'] . ', ' . $j['arquivo'] . ', ' . $j['referencia'] . ', ' . $j['id_referencia'] . ', ' . $j['multa'] . ', ' . $j['juros'] . ', ' . $j['desconto'] . ', ' . $j['subtotal'] . ')" title="Editar Dados"><i class="fa fa-edit text-primary ico-grande"></i></a>
                <li class="dropdown head-dpdn2" style="display:inline-block;">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Excluir"><i class="fa-solid fa-trash-can text-danger ico-grande"></i></a>
                    <ul class="dropdown-menu" style="margin-left:-230px;"><li><div class="notification_desc2"><p>Confirmar Exclusão? <a href="#" onclick="excluir(\'' . $id . '\')"><span class="text-danger">Sim</span></a></p></div></li></ul>
                </li>
                <a href="#" onclick="mostrar(' . $j['descricao'] . ', ' . $j['paciente_nome'] . ', ' . $j['valorF'] . ', ' . $j['data_vencimentoF'] . ', ' . $j['data_lancamentoF'] . ', ' . $j['data_pagamentoF_js'] . ', ' . $j['forma_pagamento_nome'] . ', ' . $j['frequencia_nome'] . ', ' . $j['obs'] . ', ' . $j['arquivo'] . ', ' . $j['referencia'] . ', ' . $j['id_referencia'] . ', ' . $j['multa'] . ', ' . $j['juros'] . ', ' . $j['desconto'] . ', ' . $j['subtotal'] . ')" title="Mostrar Dados"><i class="fa fa-info-circle text-dark ico-grande"></i></a>
            </td>
        </tr>';
    }

    echo '</tbody><div class="centro-pequeno" id="mensagem-excluir"></div></table>';
} else {
    echo 'Nenhum Registro Encontrado!';
}
?>

<script>
    $(document).ready(function() {
        $('#btn-deletar').hide();
        var table = $('#tabela').DataTable({
            "ordering": false,
            "stateSave": true,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
            },
            "columnDefs": [{
                "className": "dt-center",
                "targets": "_all"
            }]
        });
        $('#tabela_wrapper').addClass('tabela-pequena');
    });
</script>

<script type="text/javascript">
    function editar(id, descricao, paciente, valor, data_vencimento, data_lancamento, data_pagamento, forma_pagamento, frequencia, obs, arquivo, referencia, id_referencia, multa, juros, desconto, subtotal) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Registro');
        $('#id').val(id);
        $('#descricao-perfil').val(descricao);
        $('#paciente-perfil').val(paciente);
        $('#valor-conta').val(valor);
        $('#vencimento-conta').val(data_vencimento);
        $('#pagamento-conta').val(data_pagamento);
        $('#forma_pagamento').val(forma_pagamento);
        $('#frequencia').val(frequencia);
        $('#obs-perfil').val(obs);
        if (arquivo && arquivo !== 'sem-foto.png') {
            $('#target-arquivo').attr("src", "./images/receber/" + arquivo);
        } else {
            $('#target-arquivo').attr("src", "./images/receber/sem-foto.png");
        }
        $('#modalForm').modal('show');
    }

    function mostrar(descricao, paciente, valor, data_vencimento, data_lancamento, data_pagamento, forma_pagamento, frequencia, obs, arquivo, referencia, id_referencia, multa, juros, desconto, subtotal) {
        $('#titulo_dados').text('Detalhes: ' + descricao);
        $('#descricao_dados-cli').text(descricao);
        $('#paciente_dados-cli').text(paciente);
        $('#valor_dados-cli').text(valor);
        $('#vencimento_dados-cli').text(data_vencimento && data_vencimento !== '-' ? data_vencimento : '-');
        $('#lancamento_dados-cli').text(data_lancamento && data_lancamento !== '-' ? data_lancamento : '-');
        $('#pagamento_dados-cli').text(data_pagamento && data_pagamento !== 'Não pago' ? data_pagamento : 'Não pago');
        $('#forma_pagamento_dados-cli').text(forma_pagamento);
        $('#frequencia_dados-cli').text(frequencia);
        $('#obs_dados-cli').text(obs || '-');
        if (arquivo && arquivo !== 'sem-foto.png' && arquivo !== '') {
            $('#target-arquivo-dados').attr('src', './images/receber/' + arquivo).show();
            $('#link-arquivo-dados').attr('href', './images/receber/' + arquivo).show();
        } else {
            $('#target-arquivo-dados').attr('src', './images/receber/sem-foto.png');
            $('#link-arquivo-dados').hide();
        }
        $('#multa_dados-cli').text(multa ? 'R$ ' + multa : '-');
        $('#juros_dados-cli').text(juros ? 'R$ ' + juros : '-');
        $('#desconto_dados-cli').text(desconto ? '- R$ ' + desconto : '-');
        $('#subtotal_dados-cli').text(subtotal ? 'R$ ' + subtotal : '-');
        if (referencia && referencia !== '') {
            $('#referencia_dados-cli').text(referencia);
            $('#id-referencia_dados-cli').text(id_referencia);
            $('#row-referencia').show();
        } else {
            $('#row-referencia').hide();
        }
        $('#modalDados').modal('show');
    }

    function limparCampos() {
        $('#id, #descricao-perfil, #paciente-perfil, #valor-conta, #vencimento-conta, #pagamento-conta, #forma_pagamento, #frequencia, #obs-perfil').val('');
        $('#arquivo-conta').val('');
        $('#target-arquivo').attr('src', './images/receber/sem-foto.png');
        $('#mensagem').text('').removeClass('text-danger');
    }

    function selecionar(id) {
        var ids = $('#ids').val();
        if ($('#seletor-' + id).is(":checked")) {
            $('#ids').val(ids + id + '-');
        } else {
            $('#ids').val(ids.replace(id + '-', ''));
        }
        $('#btn-deletar').toggle($('#ids').val() !== "");
    }

    function deletarSel() {
        var ids = $('#ids').val().split("-");
        for (var i = 0; i < ids.length - 1; i++) {
            if (ids[i]) excluir(ids[i]);
        }
        limparCampos();
    }

    function excluir(id) {
        $.ajax({
            url: 'paginas/' + pag + "/excluir.php",
            method: 'POST',
            data: {
                id: id
            },
            dataType: "html",
            success: function(mensagem) {
                if (mensagem.trim() === "Excluído com Sucesso") {
                    listar();
                } else {
                    $('#mensagem-excluir').addClass('text-danger').text(mensagem);
                }
            }
        });
    }
</script>