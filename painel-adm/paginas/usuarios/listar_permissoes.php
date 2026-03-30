<?php
$tabela = 'permissoes';
require_once("../../../conexao.php");

$id_user = @$_POST['id'] ?? 0;

// ============================================================
// 1. ACESSOS SEM GRUPO (grupo = 0)
// ============================================================
$acessosSemGrupo = $pdo->query("SELECT * FROM acessos WHERE grupo = 0 ORDER BY nome ASC");
$listaSemGrupo = $acessosSemGrupo->fetchAll(PDO::FETCH_ASSOC);

if (count($listaSemGrupo) > 0) {
    echo '<div class="mb-3"><span class="titulo-grupo"><strong>Sem Grupo</strong></span></div><div class="row">';
    
    foreach ($listaSemGrupo as $acesso) {
        $idAcesso = $acesso['id'];
        $nomeAcesso = htmlspecialchars($acesso['nome']);
        
        // Verifica permissão do usuário
        $verifica = $pdo->prepare("SELECT id FROM $tabela WHERE usuario = :usuario AND permissao = :permissao");
        $verifica->execute([':usuario' => $id_user, ':permissao' => $idAcesso]);
        $checked = ($verifica->rowCount() > 0) ? 'checked' : '';
        
        echo '<div class="col-md-4 mb-3">
                <div class="form-check">
                    <input class="form-check-input" onclick="adicionarPermissoes(' . $id_user . ', ' . $idAcesso . ')" type="checkbox" value="' . $idAcesso . '" id="acesso_' . $idAcesso . '" ' . $checked . '>
                    <label class="form-check-label font-permissoes" for="acesso_' . $idAcesso . '">' . $nomeAcesso . '</label>
                </div>
              </div>';
    }
    echo '</div><hr>'; // Fecha .row
}

// ============================================================
// 2. ACESSOS POR GRUPO
// ============================================================
$grupos = $pdo->query("SELECT * FROM grupo_acessos ORDER BY nome_grupo ASC");
$listaGrupos = $grupos->fetchAll(PDO::FETCH_ASSOC);

foreach ($listaGrupos as $grupo) {
    $idGrupo = $grupo['id'];
    $nomeGrupo = htmlspecialchars($grupo['nome_grupo']);
    
    // Busca acessos deste grupo
    $acessosDoGrupo = $pdo->prepare("SELECT * FROM acessos WHERE grupo = :grupo ORDER BY nome ASC");
    $acessosDoGrupo->execute([':grupo' => $idGrupo]);
    $listaAcessos = $acessosDoGrupo->fetchAll(PDO::FETCH_ASSOC);
    
    // Só exibe o grupo se tiver acessos
    if (count($listaAcessos) > 0) {
        echo '<div class="mb-3 mt-4"><span class="titulo-grupo"><strong>' . $nomeGrupo . '</strong></span></div><div class="row">';
        
        foreach ($listaAcessos as $acesso) {
            $idAcesso = $acesso['id'];
            $nomeAcesso = htmlspecialchars($acesso['nome']);
            
            // Verifica permissão do usuário
            $verifica = $pdo->prepare("SELECT id FROM $tabela WHERE usuario = :usuario AND permissao = :permissao");
            $verifica->execute([':usuario' => $id_user, ':permissao' => $idAcesso]);
            $checked = ($verifica->rowCount() > 0) ? 'checked' : '';
            
            echo '<div class="col-md-4 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" onclick="adicionarPermissoes(' . $id_user . ', ' . $idAcesso . ')" type="checkbox" value="' . $idAcesso . '" id="acesso_' . $idAcesso . '" ' . $checked . '>
                        <label class="form-check-label font-permissoes" for="acesso_' . $idAcesso . '">' . $nomeAcesso . '</label>
                    </div>
                  </div>';
        }
        echo '</div><hr>'; // Fecha .row
    }
}
?>