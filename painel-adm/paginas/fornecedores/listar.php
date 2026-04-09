<?php
$tabela = 'fornecedores';
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
	            <th>Telefone</th>	    
	            <th class="esc">Email</th>	
	            <th>CNPJ</th>	
	            <th>Ações</th>

	        </tr> 

	    </thead> 

	    <tbody>	

HTML;

    for ($i = 0; $i < $linhas; $i++) {
        $id = $res[$i]['id'];
        $nome = $res[$i]['nome'];
        $email = $res[$i]['email'];
        $telefone = $res[$i]['telefone'];
        $cnpj = $res[$i]['cnpj'];
        $cep = $res[$i]['cep'];
        $rua = $res[$i]['rua'];
        $numero = $res[$i]['numero'];
        $bairro = $res[$i]['bairro'];
        $cidade = $res[$i]['cidade'];
        $estado = $res[$i]['estado'];
        $ativo = $res[$i]['ativo'];
        $data = $res[$i]['data_criacao'];
        $obs = $res[$i]['observacoes'];

        $dataF = date('d/m/Y', strtotime($data));

        if ($ativo == 'Sim') {
            $icone = 'fa-square-check';
            $titulo_link = 'Desativar Usuário';
            $acao = 'Não';
            $classe_ativo = '';
        } else {
            $icone = 'fa-square';
            $titulo_link = 'Ativar Usuário';
            $acao = 'Sim';
            $classe_ativo = '#c4c4c4';
        }

echo <<<HTML
            <tr style="color:{$classe_ativo}">
                <td>
                    <input type="checkbox" id="seletor-{$id}" class="form-check-input" onchange="selecionar('{$id}')">
                        {$nome}
                </td>
                <td>{$telefone}</td>
                <td class="esc">{$email}</td>
                <td>{$cnpj}</td>
                <td>
	                <a href="#" onclick="editar('{$id}',
                                                '{$nome}',
                                                '{$email}',
                                                '{$telefone}',
                                                '{$cnpj}',
                                                '{$cep}',
                                                '{$rua}',
                                                '{$numero}',
                                                '{$bairro}',
                                                '{$cidade}',
                                                '{$estado}',
                                                '{$ativo}',
                                                '{$dataF}',
                                                '{$obs}')" title="Editar Dados">
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

                    <a href="#" onclick="mostrar('{$nome}',
                                                 '{$email}',
                                                 '{$telefone}',
                                                 '{$cnpj}',
                                                 '{$cep}',
                                                 '{$rua}',
                                                 '{$numero}',
                                                 '{$bairro}',
                                                 '{$cidade}',
                                                 '{$estado}',
                                                 '{$ativo}',
                                                 '{$dataF}',
                                                 '{$obs}')" title="Mostrar Dados">
                                                    <i class="fa fa-info-circle text-dark ico-grande"></i>
                    </a>


                    <a href="#" onclick="ativar('{$id}', 
                                                '{$acao}')" title="{$titulo_link}">
                                                    <i class="fa {$icone} text-success ico-grande"></i>
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

        // ✅ Aplica a classe .tabela-pequena no wrapper do DataTables
        $('#tabela_wrapper').addClass('tabela-pequena');
    });
</script>

<script type="text/javascript">
    function editar(id, nome, email, telefone, cnpj, cep, rua, numero, bairro, cidade, estado, ativo, data, obs) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Registro');

        $('#id').val(id);
        $('#nome-perfil').val(nome);
        $('#email-perfil').val(email);
        $('#telefone-perfil').val(telefone);
        $('#cnpj-perfil').val(cnpj);

        // Endereço
        $('#cep-perfil').val(cep);
        $('#rua-perfil').val(rua);
        $('#numero-perfil').val(numero);
        $('#bairro-perfil').val(bairro);
        $('#cidade-perfil').val(cidade);
        $('#estado-perfil').val(estado);

        // Cargo/Nível e status
        $('#ativo').val(ativo);

        // Data e foto
        $('#data_dados').text(data); // Ajuste o ID conforme seu modal
        $('#obs-perfil').val(obs); // Se tiver um campo de observações no modal

        // Abre o modal
        $('#modalForm').modal('show'); // Ou $('#modalPerfil').modal('show') se for o mesmo modal
    }

    function mostrar(nome, email, telefone, cnpj, cep, rua, numero,
        bairro, cidade, estado, ativo, data, obs) {

        // Dados básicos
        $('#nome_dados-for').text(nome);
        $('#email_dados-for').text(email); // ← Adicionar este campo no modal (veja abaixo)
        $('#cnpj_dados-for').text(cnpj);
        $('#telefone_dados-for').text(telefone);


        // Endereço
        $('#cep_dados-for').text(cep); // ← Corrigido: era 'ep_dados-for'
        $('#rua_dados-for').text(rua);
        $('#numero_dados-for').text(numero);
        $('#bairro_dados-for').text(bairro);
        $('#cidade_dados-for').text(cidade);
        $('#estado_dados-for').text(estado);

        // Status e data
        $('#ativo_dados-for').text(ativo);
        $('#data_dados-for').text(data); // ← Corrigido: era '.text(ativo)'
        $('#obs_dados-for').text(obs || '-');

        // Abre o modal CORRETO
        $('#modalDados').modal('show');
    }

    function limparCampos() {
        // Dados básicos
        $('#id').val('');
        $('#nome-perfil').val('');
        $('#email-perfil').val('');
        $('#telefone-perfil').val('');
        $('#cnpj-perfil').val('');

        // Endereço
        $('#cep-perfil').val('');
        $('#rua-perfil').val('');
        $('#numero-perfil').val('');
        $('#bairro-perfil').val('');
        $('#cidade-perfil').val('');
        $('#estado-perfil').val('');
        $('#obs-perfil').val('');

        // Cargo/Nível e status (reseta para o primeiro option)
        $('#ativo').val('Sim').change();

        // Mensagem de erro/sucesso
        $('#mensagem').text('').removeClass('text-danger');
    }

    function selecionar(id) {

        var ids = $('#ids').val();

        if ($('#seletor-' + id).is(":checked") == true) {
            var novo_id = ids + id + '-';
            $('#ids').val(novo_id);
        } else {
            var retirar = ids.replace(id + '-', '');
            $('#ids').val(retirar);
        }

        var ids_final = $('#ids').val();
        if (ids_final == "") {
            $('#btn-deletar').hide();
        } else {
            $('#btn-deletar').show();
        }
    }

    function deletarSel() {
        var ids = $('#ids').val();
        var id = ids.split("-");

        for (i = 0; i < id.length - 1; i++) {
            excluir(id[i]);
        }

        limparCampos();
    }

    function permissoes(id, nome) {

        $('#id_permissoes').val(id);
        $('#nome_permissoes').text(nome);

        $('#modalPermissoes').modal('show');
        listarPermissoes(id);
    }

    function listarPermissoes(id) {
        $.ajax({
            url: 'paginas/' + pag + "/listar_permissoes.php",
            method: 'POST',
            data: {
                id
            },
            dataType: "html",

            success: function(result) {
                $('#listar_permissoes').html(result);
                $('#mensagem_permissao').text('');
            }
        });
    }

    function adicionarPermissoes(id, acesso) {
        $.ajax({
            url: 'paginas/' + pag + "/add_permissoes.php",
            method: 'POST',
            data: {
                id: id,
                acesso: acesso
            },
            dataType: "text",
            success: function(result) {
                // Só recarrega a lista se a gravação confirmou
                if (result.trim() === 'inserido' || result.trim() === 'removido') {
                    listarPermissoes(id);
                } else {
                    console.log('Erro ao salvar permissão: ' + result);
                    $('#mensagem_permissao').addClass('text-danger').text('Erro ao salvar: ' + result);
                }
            },
            error: function(xhr, status, error) {
                console.log('Erro AJAX: ' + error);
                $('#mensagem_permissao').addClass('text-danger').text('Erro de conexão');
            }
        });
    }''

    function marcarTodos() {
        var id_user = $('#id_permissoes').val();
        var marcado = $('#input_todos').is(':checked'); // ← Verifica se está marcado

        $.ajax({
            url: 'paginas/' + pag + "/add_all_permissoes.php",
            method: 'POST',
            data: {
                id: id_user,
                acao: marcado ? 'marcar_todos' : 'desmarcar_todos' // ← Envia a ação correta
            },
            dataType: "html",
            success: function(result) {
                listarPermissoes(id_user);
            }
        });
    }

    function excluir(id) {
        $.ajax({
            url: 'paginas/' + pag + "/excluir.php",
            method: 'POST',
            data: {
                id
            },
            dataType: "html",

            success: function(mensagem) {
                if (mensagem.trim() == "Excluído com Sucesso") {
                    listar();
                } else {
                    $('#mensagem-excluir').addClass('text-danger')
                    $('#mensagem-excluir').text(mensagem)
                }
            }
        });
    }
</script>