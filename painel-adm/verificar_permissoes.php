<?php
if (defined('PERMISSOES_JA_CARREGADAS')) { return; }
define('PERMISSOES_JA_CARREGADAS', true);
@session_start();
require_once("../conexao.php");
$id_user = $_SESSION['id_user'];

// Inicializa todas como 'ocultar' (padrão: sem acesso)
$home = 'ocultar';
$configuracoes = 'ocultar';
$usuarios = 'ocultar';
$grupo_acessos = 'ocultar';
$acessos = 'ocultar';
$cargos = 'ocultar';
$pagar = 'ocultar';
$receber = 'ocultar';
$menu_pessoas = 'ocultar';
$menu_cadastros = 'ocultar';
$menu_financeiro = 'ocultar';
$perfil_modal = 'ocultar';

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
            } else if ($chave_acesso == 'usuarios') {
                $usuarios = '';
            } else if ($chave_acesso == 'grupo_acessos') {
                $grupo_acessos = '';
            } else if ($chave_acesso == 'acessos') {
                $acessos = '';
            } else if ($chave_acesso == 'cargos') {
                $cargos = '';
            } else if ($chave_acesso == 'pagar') {
                $pagar = '';
            } else if ($chave_acesso == 'receber') {
                $receber = '';
            } else if ($chave_acesso == 'perfil') {
                $perfil_modal = '';  // ← Controla modal Perfil
            }
        }
    }
}

// ✅ Define página inicial baseada nas permissões (SOMENTE páginas reais)
if ($home != 'ocultar') {
    $pag_inicial = 'home';
} else if ($usuarios != 'ocultar') {
    $pag_inicial = 'usuarios';
} else if ($cargos != 'ocultar') {
    $pag_inicial = 'cargos';
} else if ($grupo_acessos != 'ocultar') {
    $pag_inicial = 'grupo_acessos';
} else if ($acessos != 'ocultar') {
    $pag_inicial = 'acessos';
} else if ($configuracoes != 'ocultar') {
    $pag_inicial = 'configuracoes';
} else {
    $pag_inicial = 'home';  // Fallback seguro
}

// Define visibilidade dos grupos de menu
if ($usuarios == 'ocultar') {
    $menu_pessoas = 'ocultar';
} else {
    $menu_pessoas = '';
}

if ($grupo_acessos == 'ocultar' AND $acessos == 'ocultar' AND $cargos == 'ocultar') {
    $menu_cadastros = 'ocultar';
} else {
    $menu_cadastros = '';
}
?>