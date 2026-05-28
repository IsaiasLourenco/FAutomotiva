<?php
$tabela = 'categorias_pecas';
require_once("../../../conexao.php");

$query = $pdo->query("SELECT * FROM $tabela ORDER BY id DESC");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {

echo <<<HTML

	<table class="table table-hover tabela-pequena" id="tabela">

	    <thead>

	        <tr>

	            <th>Nome</th>
	            <th>Ações</th>

	        </tr>

	    </thead>

	    <tbody>

HTML;

    for ($i = 0; $i < $linhas; $i++) {
        $id = $res[$i]['id'];
        $nome = $res[$i]['nome'];


echo <<<HTML
            <tr>
                <td>
                    <input type="checkbox" id="seletor-{$id}" class="form-check-input" onchange="selecionar('{$id}')">
                        {$nome}
                </td>
                <td>
	                <a href="#" onclick="editar('{$id}',
                                                '{$nome}')" title="Editar Dados">
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

        // ✅ Aplica a classe .tabela-pequena no wrapper do DataTables
        $('#tabela_wrapper').addClass('tabela-pequena');
    });
</script>

<script type="text/javascript">
    function editar(id, nome) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Registro');

        $('#id').val(id);
        $('#nome-perfil').val(nome);

        // Abre o modal
        $('#modalForm').modal('show');
    }

    function limparCampos() {
        $('#id').val('');
        $('#nome-perfil').val('');
        $('#mensagem').text('').removeClass('text-danger');
    }

    function selecionar(id) {
        var ids = $('#ids').val();
        var lista = ids ? ids.split('-').filter(function(x) { return x !== ''; }) : [];

        if ($('#seletor-' + id).is(":checked")) {
            if (!lista.includes(id)) {
                lista.push(id);
            }
        } else {
            lista = lista.filter(function(x) { return x !== id; });
        }

        $('#ids').val(lista.length > 0 ? lista.join('-') + '-' : '');
        $('#btn-deletar').toggle(lista.length > 0);
    }

    function deletarSel() {
        var ids = $('#ids').val();
        var id = ids.split("-");

        for (i = 0; i < id.length - 1; i++) {
            excluir(id[i]);
        }

        limparCampos();
    }
</script>
