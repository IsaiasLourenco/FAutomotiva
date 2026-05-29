<?php
$tabela = 'veiculos';
require_once("../../../conexao.php");

$query = $pdo->query("SELECT v.*, c.nome as cliente_nome, c.id as cliente_id
                      FROM $tabela v
                      LEFT JOIN clientes c ON v.cliente_id = c.id
                      ORDER BY v.placa ASC");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {

echo <<<HTML
	<table class="table table-hover tabela-pequena" id="tabela">
	    <thead>
	        <tr>
	            <th>Placa</th>
	            <th>Modelo</th>
	            <th>Marca</th>
	            <th>Cor</th>
	            <th>Ano</th>
	            <th>Cliente</th>
	            <th>Ações</th>
	        </tr>
	    </thead>
	    <tbody>
HTML;

    for ($i = 0; $i < $linhas; $i++) {
        $id = $res[$i]['id'];
        $placa = $res[$i]['placa'];
        $modelo = $res[$i]['modelo'] ?? '-';
        $marca = $res[$i]['marca'] ?? '-';
        $cor = $res[$i]['cor'] ?? '-';  // ✅ Captura cor
        $ano = $res[$i]['ano'] ?? '-';
        $cliente_id = $res[$i]['cliente_id'] ?? '';
        $cliente_nome = $res[$i]['cliente_nome'] ?? 'Não vinculado';
        $motor = $res[$i]['motor'] ?? '-';
        $km = $res[$i]['km_atual'] ?? '-';
        $obs = $res[$i]['observacoes'] ?? '-';

echo <<<HTML
            <tr>
                <td>
                    <input type="checkbox" id="seletor-{$id}" class="form-check-input" onchange="selecionar('{$id}')">
                    <strong>{$placa}</strong>
                </td>
                <td>{$modelo}</td>
                <td>{$marca}</td>
                <td>{$cor}</td>  <!-- ✅ Exibe cor -->
                <td>{$ano}</td>
                <td>{$cliente_nome}</td>
                <td>
                    <a href="#" onclick="editar('{$id}','{$placa}','{$cliente_id}','{$cliente_nome}','{$marca}','{$modelo}','{$ano}','{$cor}','{$motor}','{$km}','{$obs}')" title="Editar">
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
                    <a href="#" onclick="mostrar('{$placa}','{$cliente_nome}','{$marca}','{$modelo}','{$ano}','{$cor}','{$motor}','{$km}','{$obs}')" title="Ver Detalhes">
                        <i class="fa fa-info-circle text-dark ico-grande"></i>
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
    function editar(id, placa, cliente_id, cliente_nome, marca, modelo, ano, cor, motor, km, obs) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Veículo');
        $('#id').val(id);
        $('#placa').val(placa);
        $('#cliente_id').val(cliente_id !== '' ? cliente_id : '');
        $('#marca').val(marca !== '-' ? marca : '');
        $('#modelo').val(modelo !== '-' ? modelo : '');
        $('#ano').val(ano !== '-' ? ano : '');
        $('#cor').val(cor !== '-' ? cor : '');  // ✅ Preenche cor
        $('#motor').val(motor !== '-' ? motor : '');
        $('#km_atual').val(km !== '-' ? km : '');
        $('#observacoes').val(obs !== '-' ? obs : '');
        $('#modalForm').modal('show');
    }

    function mostrar(placa, cliente_nome, marca, modelo, ano, cor, motor, km, obs) {
        $('#placa_dados-vei').text(placa);
        $('#cliente_dados-vei').text(cliente_nome !== 'Não vinculado' ? cliente_nome : 'Não informado');
        $('#marca_dados-vei').text(marca !== '-' ? marca : 'Não informado');
        $('#modelo_dados-vei').text(modelo !== '-' ? modelo : 'Não informado');
        $('#ano_dados-vei').text(ano !== '-' ? ano : 'Não informado');
        $('#cor_dados-vei').text(cor !== '-' ? cor : 'Não informado');  // ✅ Exibe cor
        $('#motor_dados-vei').text(motor !== '-' ? motor : 'Não informado');
        $('#km_dados-vei').text(km !== '-' ? km + ' km' : 'Não informado');
        $('#obs_dados-vei').text(obs !== '-' ? obs : 'Nenhuma');
        $('#modalDados').modal('show');
    }

    function limparCampos() {
        $('#id').val('');
        $('#placa').val('');
        $('#cliente_id').val('');
        $('#marca').val('');
        $('#modelo').val('');
        $('#ano').val('');
        $('#cor').val('');  // ✅ Limpa cor
        $('#motor').val('');
        $('#km_atual').val('');
        $('#observacoes').val('');
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
