<?php
require_once("../../../conexao.php");
$id_conta = @$_POST['id'] ?? 0;

if (!$id_conta) { echo "<tr><td colspan='4' class='text-center text-muted py-3'>Nenhum arquivo</td></tr>"; exit; }

$stmt = $pdo->prepare("SELECT * FROM arquivos_conta WHERE id_conta = :id ORDER BY data_upload DESC");
$stmt->execute([':id' => $id_conta]);
$arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($arquivos)) { echo "<tr><td colspan='4' class='text-center text-muted py-3'>Nenhum arquivo anexado</td></tr>"; exit; }

foreach ($arquivos as $a) {
    $ext = strtolower($a['tipo_arquivo']);
    $nome_orig = $a['nome_arquivo'];
    $id_arq = $a['id'];
    
    $icone = 'fa-file'; $cor = 'text-secondary';
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) { $icone = 'fa-file-image'; $cor = 'text-success'; }
    elseif ($ext == 'pdf') { $icone = 'fa-file-pdf'; $cor = 'text-danger'; }
    elseif (in_array($ext, ['xls','xlsx'])) { $icone = 'fa-file-excel'; $cor = 'text-success'; }
    elseif (in_array($ext, ['doc','docx'])) { $icone = 'fa-file-word'; $cor = 'text-primary'; }
    
    $dataF = $a['data_upload'] ? date('d/m/Y', strtotime($a['data_upload'])) : '-';
    
    // ✅ Define quais botões mostrar
    $pode_visualizar = in_array($ext, ['jpg','jpeg','png','gif','webp','pdf']);
    
    echo <<<HTML
<tr>
    <td class="text-center"><i class="fa-solid {$icone} {$cor} fa-lg"></i></td>
    <td>
        <strong class="d-block text-truncate" style="max-width:250px;" title="{$nome_orig}">{$nome_orig}</strong>
        <small class="text-muted">.{$ext}</small>
    </td>
    <td class="text-center"><small class="text-muted">{$dataF}</small></td>
    <td class="text-center">
        <div class="btn-group btn-group-sm">
HTML;

    // ✅ Botão Visualizar (só para imagens e PDF)
    if ($pode_visualizar) {
        echo '<a href="paginas/receber/download-arquivo.php?id='.$id_arq.'&acao=visualizar" target="_blank" class="btn btn-outline-primary" title="Visualizar">
                  <i class="fa fa-eye"></i>
              </a>';
    }
    
    // ✅ Botão Baixar (para todos)
    echo '<a href="paginas/receber/download-arquivo.php?id='.$id_arq.'&acao=baixar" target="_blank" class="btn btn-outline-success" title="Baixar">
              <i class="fa fa-download"></i>
           </a>';
    
    // ✅ Botão Excluir
    echo '<button onclick="excluirArquivo('.$id_arq.', '.$id_conta.')" class="btn btn-outline-danger" title="Excluir">
              <i class="fa fa-trash"></i>
           </button>';

    echo <<<HTML
        </div>
    </td>
</tr>
HTML;
}
?>