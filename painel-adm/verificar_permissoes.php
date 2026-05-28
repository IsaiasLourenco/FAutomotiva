<?php
if (defined('PERMISSOES_JA_CARREGADAS')) {
    return;
}
define('PERMISSOES_JA_CARREGADAS', true);
@session_start();
require_once("../conexao.php");
$id_user = $_SESSION['id_user'];

// Inicializa todas como 'ocultar' (padrão: sem acesso)
// menu superior
$home               = 'ocultar';
$configuracoes      = 'ocultar';
$perfil_modal       = 'ocultar';

// grupo pessoas
$menu_pessoas       = 'ocultar';
$usuarios           = 'ocultar';
$clientes          = 'ocultar';
$fornecedores       = 'ocultar';

//grupo cadastros
$menu_cadastros     = 'ocultar';
$pecas              = 'ocultar';
$veiculos           = 'ocultar';
$categorias_pecas   = 'ocultar';
$marcas             = 'ocultar';
$formas_pagamento   = 'ocultar';
$frequencias        = 'ocultar';
$cargos             = 'ocultar';
$grupo_acessos      = 'ocultar';
$acessos            = 'ocultar';

//grupo financeiro
$menu_financeiro    = 'ocultar';
$pagar              = 'ocultar';
$receber            = 'ocultar';
$relfin             = 'ocultar';
$relsin             = 'ocultar';
$relbal             = 'ocultar';


// Busca permissões do usuário
$permissoes = $pdo->query("SELECT * FROM permissoes WHERE usuario = '$id_user'");
$permitidos = $permissoes->fetchAll(PDO::FETCH_ASSOC);
$total_permissoes = count($permitidos);

if ($total_permissoes > 0) {
    for ($i = 0; $i < $total_permissoes; $i++) {
        $permissao = $permitidos[$i]['permissao'];

        $user_acessos = $pdo->query("SELECT * FROM acessos WHERE id = '$permissao'");
        $acessos_permitidos = $user_acessos->fetchAll(PDO::FETCH_ASSOC);

        if (count($acessos_permitidos) > 0) {
            $chave_acesso = $acessos_permitidos[0]['chave'];

            if ($chave_acesso == 'home') {
                $home = '';
            } else if ($chave_acesso == 'configuracoes') {
                $configuracoes = '';  // ← Controla MENU lateral E modal Config
            } else if ($chave_acesso == 'perfil') {
                $perfil_modal = '';  // ← Controla modal Perfil
            } else if ($chave_acesso == 'usuarios') {
                $usuarios = '';
            } else if ($chave_acesso == 'clientes') {
                $clientes = '';
            } else if ($chave_acesso == 'fornecedores') {
                $fornecedores = '';
            } else if ($chave_acesso == 'categorias_pecas') {
                $categorias_pecas = '';
            } else if ($chave_acesso == 'marcas') {
                $marcas = '';
            } else if ($chave_acesso == 'pecas') {
                $pecas = '';
            } else if ($chave_acesso == 'veiculos') {
                $veiculos = '';
            } else if ($chave_acesso == 'formas_pagamento') {
                $formas_pagamento = '';
            } else if ($chave_acesso == 'frequencias') {
                $frequencias = '';
            } else if ($chave_acesso == 'cargos') {
                $cargos = '';
            } else if ($chave_acesso == 'grupo_acessos') {
                $grupo_acessos = '';
            } else if ($chave_acesso == 'acessos') {
                $acessos = '';
            } else if ($chave_acesso == 'pagar') {
                $pagar = '';  // ← Controla página Pagar
            } else if ($chave_acesso == 'receber') {
                $receber = '';  // ← Controla página Receber
            } else if ($chave_acesso == 'relfin') {
                $relfin = '';  // ← Controla página Relatório Financeiro
            } else if ($chave_acesso == 'relsin') {
                $relsin = '';  // ← Controla página Relatório Sintético
            } else if ($chave_acesso == 'relbal') {
                $relbal = '';  // ← Controla página Relatório Balanço Anual
            }
        }
    }
}

// ✅ Define página inicial baseada nas permissões (SOMENTE páginas reais)
if ($home != 'ocultar') {
    $pag_inicial = 'home';
} else if ($perfil_modal != 'ocultar') {
    $pag_inicial = 'perfil';
} else if ($usuarios != 'ocultar') {
    $pag_inicial = 'usuarios';
} else if ($clientes != 'ocultar') {
    $pag_inicial = 'clientes';
} else if ($fornecedores != 'ocultar') {
    $pag_inicial = 'fornecedores';
} else if ($pecas != 'ocultar') {
    $pag_inicial = 'pecas';
} else if ($veiculos != 'ocultar') {
    $pag_inicial = 'veiculos';
} else if ($categorias_pecas != 'ocultar') {
    $pag_inicial = 'categorias_pecas';
} else if ($marcas != 'ocultar') {
    $pag_inicial = 'marcas';
} else if ($formas_pagamento != 'ocultar') {
    $pag_inicial = 'formas_pagamento';
} else if ($frequencias != 'ocultar') {
    $pag_inicial = 'frequencias';
} else if ($cargos != 'ocultar') {
    $pag_inicial = 'cargos';
} else if ($grupo_acessos != 'ocultar') {
    $pag_inicial = 'grupo_acessos';
} else if ($pagar != 'ocultar') {
    $pag_inicial = 'pagar';
} else if ($receber != 'ocultar') {
    $pag_inicial = 'receber';
} else if ($relfin != 'ocultar') {
    $pag_inicial = 'relfin';
} else if ($relsin != 'ocultar') {
    $pag_inicial = 'relsin';
} else if ($relbal != 'ocultar') {
    $pag_inicial = 'relbal';
} else if ($acessos != 'ocultar') {
    $pag_inicial = 'acessos';
} else if ($configuracoes != 'ocultar') {
    $pag_inicial = 'configuracoes';
} else {
    $pag_inicial = 'home';  // Fallback seguro
}

// ✅ Define visibilidade do grupo PESSOAS
if ($usuarios == 'ocultar' and $clientes == 'ocultar' and $fornecedores == 'ocultar') {
    $menu_pessoas = 'ocultar';
} else {
    $menu_pessoas = '';
}

// ✅ Define visibilidade do grupo CADASTROS (CORREÇÃO: inclui todos os itens)
if (
    $pecas == 'ocultar'
    and $veiculos == 'ocultar'
    and $categorias_pecas == 'ocultar'
    and $marcas == 'ocultar'
    and $formas_pagamento == 'ocultar'
    and $frequencias == 'ocultar'
    and $cargos == 'ocultar'
    and $grupo_acessos == 'ocultar'
    and $acessos == 'ocultar'
) {
    $menu_cadastros = 'ocultar';
} else {
    $menu_cadastros = '';
}

// ✅ Define visibilidade do grupo FINANCEIRO (NOVO - não existia!)
if ($pagar == 'ocultar' and $receber == 'ocultar') {
    $menu_financeiro = 'ocultar';
} else {
    $menu_financeiro = '';
}
