<?php
$tabela = 'usuarios';
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
	            <th>Nível</th>	
	            <th class="esc">Foto</th>	
	            <th>Ações</th>

	        </tr> 

	    </thead> 

	    <tbody>	

HTML;

    for ($i = 0; $i < $linhas; $i++) {
        $id = $res[$i]['id'];
        $nome = $res[$i]['nome'];
        $email = $res[$i]['email'];
        $senha = $res[$i]['senha'];
        $nivel = $res[$i]['cargo'];
        $telefone = $res[$i]['telefone'];
        $cpf = $res[$i]['cpf'];
        $cep = $res[$i]['cep'];
        $rua = $res[$i]['rua'];
        $numero = $res[$i]['numero'];
        $bairro = $res[$i]['bairro'];
        $cidade = $res[$i]['cidade'];
        $estado = $res[$i]['estado'];
        $ativo = $res[$i]['ativo'];
        $data = $res[$i]['data_criacao'];
        $foto = $res[$i]['foto'];

        $cargo = $pdo->prepare("SELECT * FROM cargos WHERE id = :id");
        $cargo->bindValue(":id", $nivel);
        $cargo->execute();
        $res_cargo = $cargo->fetchAll(PDO::FETCH_ASSOC);
        $cargo_nome = $res_cargo[0]['nome'] ?? 'Nível Desconecido';

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

        $mostrar_adm = '';
        if ($cargo_nome == 'Administrador') {
            $senha = '******';
            $mostrar_adm = 'ocultar';
        }


        echo <<<HTML
            <tr style="color:{$classe_ativo}">
                <td>
                    <input type="checkbox" id="seletor-{$id}" class="form-check-input" onchange="selecionar('{$id}')">
                        {$nome}
                </td>
                <td>{$telefone}</td>
                <td class="esc">{$email}</td>
                <td>{$cargo_nome}</td>
                <td class="esc"><img src="images/perfil/{$foto}" width="25px"></td>
                <td>
	                <a href="#" onclick="editar('{$id}',
                                                '{$nome}',
                                                '{$email}',
                                                '{$senha}',
                                                '{$nivel}',
                                                '{$telefone}',
                                                '{$cpf}',
                                                '{$cep}',
                                                '{$rua}',
                                                '{$numero}',
                                                '{$bairro}',
                                                '{$cidade}',
                                                '{$estado}',
                                                '{$ativo}',
                                                '{$dataF}',
                                                '{$foto}')" title="Editar Dados">
                                                    <i class="fa fa-edit text-primary ico-grande"></i>
                    </a>

	                <li class="dropdown head-dpdn2" style="display: inline-block;">
		                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Excluir Registro">
                            <i class="fa-solid fa-trash-can text-danger ico-grande"></i>
                        </a>

		                <ul class="dropdown-menu" style="margin-left:-230px;">

		                    <li>
		                        <div class="notification_desc2">
		                            <p>Confirmar Exclusão? 
                                        <a href="#" onclick="excluir('{$id}')">
                                            <span class="text-danger">Sim</span>
                                        </a>
                                    </p>
		                        </div>
		                    </li>										
		                </ul>
                    </li>

                    <a href="#" onclick="mostrar('{$nome}',
                                                 '{$email}',
                                                 '{$senha}',
                                                '{$cargo_nome}',
                                                '{$telefone}',
                                                '{$cpf}',
                                                '{$cep}',
                                                '{$rua}',
                                                '{$numero}',
                                                '{$bairro}',
                                                '{$cidade}',
                                                '{$estado}',
                                                '{$ativo}',
                                                '{$dataF}',
                                                '{$foto}')" title="Mostrar Dados">
                                                    <i class="fa fa-info-circle text-dark ico-grande"></i>
                    </a>


                    <a href="#" onclick="ativar('{$id}', 
                                                '{$acao}')" title="{$titulo_link}">
                                                    <i class="fa {$icone} text-success ico-grande"></i>
                    </a>


                    <a class="{$mostrar_adm}" href="#" onclick="permissoes('{$id}', 
                                                                           '{$nome}')" title="Dar Permissões">
                                                                                    <i class="fa fa-lock text-success ico-grande"></i>
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
    function editar(id, nome, email, senha, cargo_nome, telefone, cpf, cep, rua, numero, bairro, cidade, estado, ativo, data, foto) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Registro');

        $('#id').val(id);
        $('#nome-perfil').val(nome);
        $('#email-perfil').val(email);
        $('#senha-perfil').val('');
        $('#conf-senha-perfil').val('');
        $('#telefone-perfil').val(telefone);
        $('#cpf-perfil').val(cpf);

        // Endereço
        $('#cep-perfil').val(cep);
        $('#rua-perfil').val(rua);
        $('#numero-perfil').val(numero);
        $('#bairro-perfil').val(bairro);
        $('#cidade-perfil').val(cidade);
        $('#estado-perfil').val(estado);

        // Cargo/Nível e status
        $('#nivel').val(cargo_nome); // Se for select pelo nome, ou use o ID se tiver
        $('#ativo').val(ativo);

        // Data e foto
        $('#data_dados').text(data); // Ajuste o ID conforme seu modal
        $('#target-usu').attr("src", "images/perfil/" + foto);

        // Abre o modal
        $('#modalForm').modal('show'); // Ou $('#modalPerfil').modal('show') se for o mesmo modal
    }

    function mostrar(nome, email, senha, cargo_nome, telefone, cpf, cep, rua, numero,
        bairro, cidade, estado, ativo, data, foto) {

        // Dados básicos
        $('#nome_dados-cli').text(nome);
        $('#email_dados-cli').text(email); // ← Adicionar este campo no modal (veja abaixo)
        $('#cpf_dados-cli').text(cpf);
        $('#telefone_dados-cli').text(telefone);
        $('#cargo_dados-cli').text(cargo_nome); // ← Adicionar este campo no modal (veja abaixo)


        // Endereço
        $('#cep_dados-cli').text(cep); // ← Corrigido: era 'ep_dados-cli'
        $('#rua_dados-cli').text(rua);
        $('#numero_dados-cli').text(numero);
        $('#bairro_dados-cli').text(bairro);
        $('#cidade_dados-cli').text(cidade);
        $('#estado_dados-cli').text(estado);

        // Status e data
        $('#ativo_dados-cli').text(ativo);
        $('#data_dados-cli').text(data); // ← Corrigido: era '.text(ativo)'

        // Foto (opcional, se quiser exibir)
        // ✅ Atualiza a foto
        if (foto && foto !== 'sem-foto.jpg') {
            $('#foto_dados-cli').attr('src', 'images/perfil/' + foto);
        } else {
            $('#foto_dados-cli').attr('src', 'images/perfil/sem-foto.jpg');
        }

        // Abre o modal CORRETO
        $('#modalDados').modal('show');
    }

    function limparCampos() {
        // Dados básicos
        $('#id').val('');
        $('#nome-perfil').val('');
        $('#email-perfil').val('');
        $('#senha-perfil').val('');
        $('#conf-senha-perfil').val('');
        $('#telefone-perfil').val('');
        $('#cpf-perfil').val('');

        // Endereço
        $('#cep-perfil').val('');
        $('#rua-perfil').val('');
        $('#numero-perfil').val('');
        $('#bairro-perfil').val('');
        $('#cidade-perfil').val('');
        $('#estado-perfil').val('');

        // Cargo/Nível e status (reseta para o primeiro option)
        $('#nivel').prop('selectedIndex', 0).change();
        $('#ativo').val('Sim').change();

        // Foto (reseta input file e preview)
        $('#foto-perfil').val('');
        $('#target-usu').attr('src', 'images/perfil/sem-foto.jpg');

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
        var id_usuario = $('#id_permissoes').val();
        var marcado = $('#input_todos').is(':checked'); // ← Verifica se está marcado

        $.ajax({
            url: 'paginas/' + pag + "/add_all_permissoes.php",
            method: 'POST',
            data: {
                id: id_usuario,
                acao: marcado ? 'marcar_todos' : 'desmarcar_todos' // ← Envia a ação correta
            },
            dataType: "html",
            success: function(result) {
                listarPermissoes(id_usuario);
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