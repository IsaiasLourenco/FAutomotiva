// FAutomotiva/js/mascaras.js - VERSÃO ORIGINAL + correção mínima
$(document).ready(function() {

    $('.cpf').mask('000.000.000-00');
    $('.cnpj').mask('00.000.000/0000-00');

    $('#telefone').mask('(00) 00000-0000');
    $('#cep').mask('00000-000');
    $('#cpf').mask('000.000.000-00');
    $('#cnpj').mask('00.000.000/0000-00');

    $('#cnpj-for').mask('00.000.000/0000-00');
    $('#cep-for').mask('00000-000');
    $('#telefone-for').mask('(00) 00000-0000');

    $('#cep-perfil').mask('00000-000');
    $('#telefone-perfil').mask('(00) 00000-0000');
    $('#cpf-perfil').mask('000.000.000-00');

    $('#cep-cli').mask('00000-000');
    $('#telefone-cli').mask('(00) 00000-0000');
    $('#cpf-cli').mask('000.000.000-00');

    $('#cep-funcionario').mask('00000-000');
    $('#telefone-funcionario').mask('(00) 00000-0000');
    $('#cpf-funcionario').mask('000.000.000-00');

    // ✅ CAMPOS DO MODAL CONFIG (mantém IDs com hífen para buscarCepModal.js funcionar)
    $('#cep-sistema').mask('00000-000');
    $('#cnpj_sistema').mask('00.000.000/0000-00');
    $('#telefone_fixo').mask('(00) 00000-0000');
    $('#telefone_sistema').mask('(00) 00000-0000');

    $('#cep-finalizar').mask('00000-000');

    $('#cep-paciente').mask('00000-000');
    $('#telefone-paciente').mask('(00) 00000-0000');

    // ✅ CORREÇÃO MÍNIMA: Re-aplica máscaras quando os modais forem ABERTOS
    $('#modalConfig, #modalPerfil').on('shown.bs.modal', function() {
        $(this).find('#cep-sistema').unmask().mask('00000-000');
        $(this).find('#cnpj_sistema').unmask().mask('00.000.000/0000-00');
        $(this).find('#telefone_fixo, #telefone_sistema').unmask().mask('(00) 00000-0000');
        $(this).find('#cep-perfil-config').unmask().mask('00000-000');
        $(this).find('#telefone-perfil').unmask().mask('(00) 00000-0000');
        $(this).find('#cpf-perfil').unmask().mask('000.000.000-00');
    });
});
