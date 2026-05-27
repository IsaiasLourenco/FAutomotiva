<?php
@session_start();

// ✅ VERIFICAÇÃO DE ASSINATURA/ATIVIDADE DO SISTEMA (CONTROLE EXTERNO)
require_once(__DIR__ . '/../conexao.php');
$config = $pdo->query("SELECT ativo FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Se NÃO estiver ativo, bloqueia TODOS (inclusive Admin da clínica)
// if ($config && $config['ativo'] !== 'Sim') {
//     session_destroy();
//     echo '<script>window.alert("Sistema bloqueado por inadimplência. Entre em contato com o desenvolvedor.")</script>';
//     echo '<script>window.location="../index.php"</script>';
//     exit;
// }

// ... resto do código original do verificar.php ...
if (!isset($_SESSION['id_user'])) {
    echo '<script>window.alert("Você não tem permissão de acesso!!")</script>';
    echo '<script>window.location="../index.php"</script>';
    exit;
}
?>
