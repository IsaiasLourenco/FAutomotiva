<?php
$tabela = 'pacientes';
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
	            <th class="esc">Telefone</th>	    
	            <th class="esc">Email</th>	
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
        $cpf = $res[$i]['cpf'];
        $telefone = $res[$i]['telefone'];
        $cep = $res[$i]['cep'];
        $rua = $res[$i]['rua'];
        $numero = $res[$i]['numero'];
        $bairro = $res[$i]['bairro'];
        $cidade = $res[$i]['cidade'];
        $estado = $res[$i]['estado'];
        $foto = $res[$i]['foto'];

        $tel_pessoaF = '55' . preg_replace('/\D/', '', $telefone) . '';

        echo <<<HTML
            <tr>
                <td>
                    <input type="checkbox" id="seletor-{$id}" class="form-check-input" onchange="selecionar('{$id}')">
                        {$nome}
                </td>
                <td class="esc">{$telefone}</td>
                <td class="esc">{$email}</td>
                <td class="esc"><img src="images/pacientes/{$foto}" width="25px"></td>
                <td>
	                <a href="#" onclick="editar('{$id}',
                                                '{$nome}',
                                                '{$email}',
                                                '{$cpf}',
                                                '{$telefone}',
                                                '{$cep}',
                                                '{$rua}',
                                                '{$numero}',
                                                '{$bairro}',
                                                '{$cidade}',
                                                '{$estado}',
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
                                                 '{$cpf}',
                                                '{$telefone}',
                                                '{$cep}',
                                                '{$rua}',
                                                '{$numero}',
                                                '{$bairro}',
                                                '{$cidade}',
                                                '{$estado}',
                                                '{$foto}')" title="Mostrar Dados">
                            <i class="fa fa-info-circle text-dark ico-grande"></i>
                    </a>

                    <a href="#" onclick="mostrarContas('{$nome}',
                                                        '{$id}')" title="Mostrar Contas">
                            <i class="fa-solid fa-wallet text-success ico-grande"></i>
                    </a>

                    <a href="https://api.whatsapp.com/send?phone=<?php echo $tel_pessoaF ?>; ?>&text=Ol%C3%A1!%20Temos%20mudanças%20no%20seu%20arranjo%20conosco." 
                       title="Enviar WhatsApp" target="_blank">
                        <i class="fa-brands fa-whatsapp text-success ico-grande"></i>
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
    function editar(id, nome, email, cpf, telefone, cep, rua, numero, bairro, cidade, estado, foto) {
        $('#mensagem').text('');
        $('#titulo_inserir').text('Editar Registro');

        $('#id').val(id);
        $('#nome-paciente').val(nome);
        $('#email-paciente').val(email);
        $('#cpf-paciente').val(cpf);
        $('#telefone-paciente').val(telefone);

        // Endereço
        $('#cep-paciente').val(cep);
        $('#rua-paciente').val(rua);
        $('#numero-paciente').val(numero);
        $('#bairro-paciente').val(bairro);
        $('#cidade-paciente').val(cidade);
        $('#estado-paciente').val(estado);

        $('#target-paciente').attr("src", "images/pacientes/" + foto);

        // Abre o modal
        $('#modalForm').modal('show'); // Ou $('#modalPerfil').modal('show') se for o mesmo modal
    }

    function mostrar(nome, email, cpf, telefone, cep, rua, numero, bairro, cidade, estado, foto) {

        // Dados básicos
        $('#nome_dados-paciente').text(nome);
        $('#email_dados-paciente').text(email); // ← Adicionar este campo no modal (veja abaixo)
        $('#cpf_dados-paciente').text(cpf);
        $('#telefone_dados-paciente').text(telefone);


        // Endereço
        $('#cep_dados-paciente').text(cep); // ← Corrigido: era 'ep_dados-cli'
        $('#rua_dados-paciente').text(rua);
        $('#numero_dados-paciente').text(numero);
        $('#bairro_dados-paciente').text(bairro);
        $('#cidade_dados-paciente').text(cidade);
        $('#estado_dados-paciente').text(estado);

        // Foto (opcional, se quiser exibir)
        // ✅ Atualiza a foto
        if (foto && foto !== 'sem-foto.jpg') {
            $('#foto_dados-paciente').attr('src', 'images/pacientes/' + foto);
        } else {
            $('#foto_dados-paciente').attr('src', 'images/pacientes/sem-foto.jpg');
        }

        // Abre o modal CORRETO
        $('#modalDados').modal('show');
    }

    function limparCampos() {
        // Dados básicos
        $('#id').val('');
        $('#nome-paciente').val('');
        $('#email-paciente').val('');
        $('#cpf-paciente').val('');
        $('#telefone-paciente').val('');

        // Endereço
        $('#cep-paciente').val('');
        $('#rua-paciente').val('');
        $('#numero-paciente').val('');
        $('#bairro-paciente').val('');
        $('#cidade-paciente').val('');
        $('#estado-paciente').val('');

        // Foto (reseta input file e preview)
        $('#foto-paciente').val('');
        $('#target-paciente').attr('src', 'images/pacientes/sem-foto.jpg');

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

    function mostrarContas(nome, id) {
        $('#titulo_contas').text(nome);
        $('#id_contas').val(id);
        $('#modalContas').modal('show');
        listarDebitos(id);
    }

    function listarDebitos(id) {
        $.ajax({
            url: 'paginas/' + pag + "/listar_debitos.php",
            method: 'POST',
            data: {
                id
            },
            dataType: "html",
            success: function(result) {
                $("#listar_debitos").html(result);
            }
        });
    }
</script>