<?php
$tabela = 'forma_pagamento';
require_once("../../../conexao.php");

$query = $pdo->query("SELECT * FROM $tabela ORDER BY id DESC");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);

if ($linhas > 0) {
    echo <<<HTML
    <table class="table table-hover tabela-pequena" id="tabela">
        <thead> 
            <tr> 
                <th>Nome do Pagamento</th>
                <th>Taxa</th>
                <th>Ações</th>
            </tr> 
        </thead> 
        <tbody>
HTML;

    for ($i = 0; $i < $linhas; $i++) {
        $id = $res[$i]['id'];
        $nome = htmlspecialchars($res[$i]['nome']);
        $taxa = htmlspecialchars($res[$i]['taxa']);
        
        echo <<<HTML
            <tr>
                <td><input type="checkbox" id="seletor-{$id}" class="form-check-input" onchange="selecionar('{$id}')">&nbsp;{$nome}</td>
                <td>{$taxa} %</td>
                <td>
                    <a href="#" onclick="editar('{$id}', '{$nome}', '{$taxa}')" title="Editar">
                        <i class="fa fa-edit text-primary ico-grande"></i>
                    </a>
                    	<li class="dropdown head-dpdn2" style="display: inline-block;">
		                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Excluir Registro">
                                <i class="fa-solid fa-trash-can text-danger ico-grande"></i>
                            </a>

		                    <ul class="dropdown-menu" style="margin-left:-230px;">

	    	                    <li>
		                            <div class="notification_desc2 centro">
	                                    <p>Confirmar Exclusão? <br>
		                                    <a href="#" onclick="excluir('{$id}')" class="btn btn-danger btn-xs">
			                                    <span>Sim</span>
		                                    </a>
	                                    </p>
                                    </div>
		                        </li>										
		                    </ul>
                        </li>
                </td>
            </tr>
HTML;
    }

    echo <<<HTML
        </tbody>
        <div class="centro-pequeno" id="mensagem-excluir"></div>
    </table>
HTML;
} else {
    echo '<div class="centro-pequeno">Nenhum Registro Encontrado!</div>';
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

    function editar(id, nome, taxa) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Grupo');
        $('#id').val(id);
        $('#nome-forma').val(nome);
        $('#taxa-forma').val(taxa);
        
        $('#modalForm').modal('show');
    }

    function excluir(id) {
        $.ajax({
            url: 'paginas/' + pag + "/excluir.php",
            method: 'POST',
            data: {
                id
            },
            success: function(mensagem) {
                if (mensagem.trim() == "Excluído com Sucesso") {
                    listar();
                } else {
                    $('#mensagem-excluir').addClass('text-danger').text(mensagem);
                }
            }
        });
    }

    function limparCampos() {
        $('#id').val('');
        $('#nome-forma').val('');
        $('#taxa-forma').val('');

        $('#btn-deletar').hide();
        $('#ids').val('');
    }

    function selecionar(id) {
        var ids = $('#ids').val();
        if ($('#seletor-' + id).is(":checked")) {
            $('#ids').val(ids + id + '-');
        } else {
            $('#ids').val(ids.replace(id + '-', ''));
        }
        $('#btn-deletar').toggle($('#ids').val() != "");
    }

    function deletarSel() {
        var ids = $('#ids').val().split("-");
        for (var i = 0; i < ids.length - 1; i++) {
            if (ids[i]) excluir(ids[i]);
        }
        $('#ids').val('');
        $('#btn-deletar').hide();
    }
</script>