// FAutomotiva/js/mascara-placa.js
// Máscara dinâmica para placa: ABC-1234 (antigo) ou ABC1D23 (Mercosul)

function aplicarMascaraPlaca(input) {
    let valor = input.value.replace(/[^A-Z0-9]/gi, '').toUpperCase();

    // Formato antigo: 3 letras + 4 números (ABC1234)
    if (/^[A-Z]{3}\d{0,4}$/.test(valor)) {
        if (valor.length <= 3) {
            input.value = valor; // Só letras
        } else if (valor.length <= 7) {
            // ABC1234 → ABC-1234
            input.value = valor.slice(0,3) + '-' + valor.slice(3);
        }
    }
    // Formato Mercosul: 3 letras + 1 número + 1 letra + 2 números (ABC1D23)
    else if (/^[A-Z]{3}\d[A-Z]?\d{0,2}$/.test(valor)) {
        if (valor.length <= 4) {
            input.value = valor; // ABC1
        } else if (valor.length === 5) {
            input.value = valor.slice(0,4) + valor.slice(4); // ABC1D
        } else if (valor.length >= 6) {
            // ABC1D23 → ABC1D23 (sem hífen no Mercosul)
            input.value = valor.slice(0,7);
        }
    }
    // Fallback: só limpa e deixa digitar
    else {
        input.value = valor.slice(0,7);
    }
}

// Aplica ao carregar a página
$(document).ready(function() {
    // Aplica máscara ao digitar
    $('input[name="placa"], #placa').on('input', function() {
        aplicarMascaraPlaca(this);
    });

    // Remove máscara ao enviar o formulário (salva só os caracteres)
    $('form#form').on('submit', function() {
        $('input[name="placa"], #placa').each(function() {
            this.value = this.value.replace(/[^A-Z0-9]/g, '');
        });
    });
});
