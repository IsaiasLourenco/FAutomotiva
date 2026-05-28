<?php
$tabela = 'pecas';
require_once("../../../conexao.php");

$query = $pdo->query("SELECT p.*, c.nome as cat_nome, m.nome as marca_nome
                      FROM $tabela p
                      LEFT JOIN categorias_pecas c ON p.categoria = c.nome
                      LEFT JOIN marcas m ON p.marca_recomendada_id = m.id
                      ORDER BY p.nome_padrao ASC");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {

echo <<<HTML
	<table class="table table-hover tabela-pequena" id="tabela">
	    <thead>
	        <tr>
	            <th>Nome</th>
	            <th>Categoria</th>
	            <th>Marca</th>
	            <th>Código</th>
	            <th>Ativo</th>
	            <th>Ações</th>
	        </tr>
	    </thead>
	    <tbody>
HTML;

        for ($i = 0; $i < $linhas; $i++) {
        $id = $res[$i]['id'];
        $nome = $res[$i]['nome_padrao'];
        $categoria = $res[$i]['cat_nome'] ?? '-';
        $marca = $res[$i]['marca_nome'] ?? '-';
        $codigo = $res[$i]['codigo_interno'] ?? '-';
        $ativo = $res[$i]['ativo'];

        // ✅ Calcula as classes ANTES do heredoc
        $status_badge = $ativo == 'Sim' ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-secondary">Inativo</span>';
        $classe_linha = $ativo == 'Sim' ? '' : 'color: #999;';
        $icone_ativo = $ativo == 'Sim' ? 'fa-square-check' : 'fa-square';
        $titulo_ativo = $ativo == 'Sim' ? 'Desativar Peça' : 'Ativar Peça';
        $acao_toggle = $ativo == 'Sim' ? 'Não' : 'Sim';

echo <<<HTML
            <tr style="{$classe_linha}">
                <td>
                    <input type="checkbox" id="seletor-{$id}" class="form-check-input" onchange="selecionar('{$id}')">
                    {$nome}
                </td>
                <td>{$categoria}</td>
                <td>{$marca}</td>
                <td>{$codigo}</td>
                <td>{$status_badge}</td>
                <td>
                    <a href="#" onclick="editar('{$id}','{$nome}','{$categoria}','{$marca}','{$codigo}','{$ativo}')" title="Editar">
                        <i class="fa fa-edit text-primary ico-grande"></i>
                    </a>
                    <li class="dropdown head-dpdn2" style="display: inline-block;">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Excluir">
                            <i class="fa-solid fa-trash-can text-danger ico-grande"></i>
                        </a>
                        <ul class="dropdown-menu" style="margin-left:-230px;">
                            <li>
                                <div class="notification_desc2 centro">
                                    <p>Confirmar Exclusão? <br>
                                    <a href="#" onclick="excluir('{$id}')" class="btn btn-danger btn-xs"><span>Sim</span></a>
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <a href="#" onclick="mostrar('{$nome}','{$categoria}','{$marca}','{$codigo}','{$ativo}')" title="Ver Detalhes">
                        <i class="fa fa-info-circle text-dark ico-grande"></i>
                    </a>
                    <a href="#" onclick="ativar('{$id}', '{$acao_toggle}')" title="{$titulo_ativo}">
                        <i class="fa {$icone_ativo} text-success ico-grande"></i>
                    </a>
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
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json" },
            "columnDefs": [{ "className": "dt-center", "targets": "_all" }]
        });
        $('#tabela_wrapper').addClass('tabela-pequena');
    });
</script>

<script type="text/javascript">
    function editar(id, nome, categoria, marca, codigo, ativo) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Peça');
        $('#id').val(id);
        $('#nome_padrao').val(nome);
        $('#categoria').val(categoria);
        $('#marca_recomendada_id').val(marca !== '-' ? marca : '');
        $('#codigo_interno').val(codigo !== '-' ? codigo : '');
        $('#ativo').val(ativo);
        $('#modalForm').modal('show');
    }

    function mostrar(nome, categoria, marca, codigo, ativo) {
        $('#nome_dados-pecas').text(nome);
        $('#categoria_dados-pecas').text(categoria);
        $('#marca_dados-pecas').text(marca);
        $('#codigo_dados-pecas').text(codigo !== '-' ? codigo : 'Não informado');
        $('#ativo_dados-pecas').text(ativo);
        $('#modalDados').modal('show');
    }

    function limparCampos() {
        $('#id').val('');
        $('#nome_padrao').val('');
        $('#categoria').val('');
        $('#marca_recomendada_id').val('');
        $('#codigo_interno').val('');
        $('#ativo').val('Sim');
        $('#mensagem').text('').removeClass('text-danger');
    }

    function selecionar(id) {
        var ids = $('#ids').val();
        var lista = ids ? ids.split('-').filter(x => x !== '') : [];
        if ($('#seletor-' + id).is(":checked")) {
            if (!lista.includes(id)) lista.push(id);
        } else {
            lista = lista.filter(x => x !== id);
        }
        $('#ids').val(lista.length > 0 ? lista.join('-') + '-' : '');
        $('#btn-deletar').toggle(lista.length > 0);
    }

    function deletarSel() {
        var ids = $('#ids').val();
        var id = ids.split("-");
        for (i = 0; i < id.length - 1; i++) { excluir(id[i]); }
        limparCampos();
    }
</script>
