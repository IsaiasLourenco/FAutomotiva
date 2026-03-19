$(document).ready(function() {    
    listar();    
} );

function listar(p1, p2, p3, p4, p5, p6){
    $.ajax({
        url: 'paginas/' + pag + "/listar.php",
        method: 'POST',
        data: {p1, p2, p3, p4, p5, p6},  // ✅ ES6 shorthand (funciona)
        dataType: "html",
        success: function(result){
            $("#listar").html(result);
            $('#mensagem-excluir').text('');
            
            // ✅ Reinicializa DataTable após carregar novo conteúdo
            if ($.fn.DataTable.isDataTable('#tabela')) {
                $('#tabela').DataTable().destroy();  // Destroi instância antiga
            }
            $('#tabela').DataTable({
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
            
            // ✅ Esconde botão excluir se não houver seleção
            $('#btn-deletar').hide();
        }
    });
}

function inserir(){
    $('#mensagem').text('');
    $('#titulo_inserir').text('Inserir Registro');
    $('#modalForm').modal('show');
    limparCampos();
}

$("#form").submit(function (event) {

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'paginas/' + pag + "/salvar.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem').text('');
            $('#mensagem').removeClass('text-danger')
            if (mensagem.trim() == "Salvo com Sucesso") {

                $('#btn-fechar').click();
                listar();          
                limparCampos();
            } else {

                $('#mensagem').addClass('text-danger')
                $('#mensagem').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});

function excluir(id){
    $.ajax({
        url: 'paginas/' + pag + "/excluir.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(mensagem){
            if (mensagem.trim() == "Excluído com Sucesso") {
                listar();
            } else {
                $('#mensagem-excluir').addClass('text-danger')
                $('#mensagem-excluir').text(mensagem)
            }
        }
    });
}

function ativar(id, acao){
    $.ajax({
        url: 'paginas/' + pag + "/mudar-status.php",
        method: 'POST',
        data: {id, acao},
        dataType: "html",

        success:function(mensagem){
            if (mensagem.trim() == "Alterado com Sucesso") {
                listar();
            } else {
                $('#mensagem-excluir').addClass('text-danger')
                $('#mensagem-excluir').text(mensagem)
            }
        }
    });
}





function baixar(id, pg, id_listar){    
    var id_usuario = localStorage.id_usu;
    var id_empresa = localStorage.id_empresa;

    if(pg != "" && pg != "undefined" && pg != undefined){        
        pag = pg;        
    }

    $.ajax({
        url: 'paginas/' + pag + "/baixar.php",
        method: 'POST',
        data: {id, id_usuario, id_empresa},
        dataType: "html",

        success:function(mensagem){
            if (mensagem.trim() == "Baixado com Sucesso") {
                if(id_listar == "" || id_listar == "undefined" || id_listar == undefined){
                    listar();
                }else{
                    listarContas(id_listar);
                    alert('Pagamento Confirmado!')
                }
                
                
            } else {
                $('#mensagem-excluir').addClass('text-danger')
                $('#mensagem-excluir').text(mensagem)
            }
        }
    });
}
