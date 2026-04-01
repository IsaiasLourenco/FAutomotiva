<?php
session_start();
$tabela = 'receber';
require_once("../../../conexao.php");

if (!isset($_SESSION['id_user'])) {
    echo "Acesso negado!";
    exit;
}
$id_usuario_logado = $_SESSION['id_user'];

$id = @$_POST['id'] ?? 0;
$descricao = $_POST['descricao'] ?? '';
$paciente = $_POST['paciente'] ?? 0;
$valor_bruto = $_POST['valor'] ?? '0';
$vencimento = $_POST['vencimento'] ?? '';
$data_pagamento = $_POST['pagamento'] ?? '';
$forma_pagamento = $_POST['forma_pagamento'] ?? 0;
$frequencia = $_POST['frequencia'] ?? 0;
$obs = $_POST['obs'] ?? '';

$valor_limpo = str_replace(['R$', '.', ','], ['', '', '.'], $valor_bruto);
$valor_base = floatval($valor_limpo);

if (empty($descricao) || empty($valor_base) || empty($vencimento)) {
    echo "Preencha os campos obrigatórios!";
    exit;
}

// Upload
$arquivo_nome = '';
if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK && !empty($_FILES['arquivo']['name'])) {
    $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'rar', 'zip', 'doc', 'docx', 'webp'])) {
        $arquivo_nome = 'receber_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = realpath(__DIR__ . '/../../images/receber/');
        if ($dest && is_dir($dest) && !move_uploaded_file($_FILES['arquivo']['tmp_name'], $dest . '/' . $arquivo_nome)) {
            echo "Erro ao salvar arquivo!";
            exit;
        }
    } else {
        echo "Formato não permitido!";
        exit;
    }
}

// Cálculos automáticos
$hoje = date('Y-m-d');
$multa_auto = $juros_auto = $desconto_auto = 0;
$taxa_auto = 2.50;
if (empty($data_pagamento) && !empty($vencimento) && $vencimento < $hoje) {
    $multa_auto = $valor_base * 0.02;
    $dias = min(max(0, (strtotime($hoje) - strtotime($vencimento)) / 86400), 30);
    $juros_auto = $valor_base * 0.0033 * $dias;
}
if (!empty($data_pagamento) && !empty($vencimento) && $data_pagamento <= $vencimento) {
    $desconto_auto = $valor_base * 0.05;
}

function parseMoeda($v)
{
    return empty($v) ? null : floatval(str_replace(['R$', '.', ''], ['', '', '.'], $v));
}
$multa_f = (isset($_POST['multa']) && $_POST['multa'] !== '') ? parseMoeda($_POST['multa']) : $multa_auto;
$juros_f = (isset($_POST['juros']) && $_POST['juros'] !== '') ? parseMoeda($_POST['juros']) : $juros_auto;
$desc_f  = (isset($_POST['desconto']) && $_POST['desconto'] !== '') ? parseMoeda($_POST['desconto']) : $desconto_auto;
$taxa_f  = (isset($_POST['taxa']) && $_POST['taxa'] !== '') ? parseMoeda($_POST['taxa']) : $taxa_auto;
$subtotal_f = $valor_base + ($multa_f ?? 0) + ($juros_f ?? 0) + ($taxa_f ?? 0) - ($desc_f ?? 0);

try {
    if (!empty($id) && $id != 0) {
        // UPDATE
        $extra = '';
        $binds = [];
        if (!empty($data_pagamento)) {
            $extra .= ", data_pagamento=:dp, usuario_pgto=:up";
            $binds[':dp'] = $data_pagamento;
            $binds[':up'] = $id_usuario_logado;
        }
        if (!empty($arquivo_nome)) {
            $extra .= ", arquivo=:arf";
            $binds[':arf'] = $arquivo_nome;
        }

        $q = $pdo->prepare("UPDATE $tabela SET descricao=:d, paciente=:p, valor=:v, data_vencimento=:dv, forma_pagamento=:fp, frequencia=:fq, obs=:o, multa=:m, juros=:j, desconto=:dc, taxa=:tx, subtotal=:st $extra WHERE id=:id");
        $q->bindValue(':id', $id, PDO::PARAM_INT);
        $q->bindValue(':d', $descricao);
        $q->bindValue(':p', $paciente, PDO::PARAM_INT);
        $q->bindValue(':v', $valor_base, PDO::PARAM_STR);
        $q->bindValue(':dv', $vencimento);
        $q->bindValue(':fp', $forma_pagamento, PDO::PARAM_INT);
        $q->bindValue(':fq', $frequencia, PDO::PARAM_INT);
        $q->bindValue(':o', $obs);
        $q->bindValue(':m', $multa_f, PDO::PARAM_STR);
        $q->bindValue(':j', $juros_f, PDO::PARAM_STR);
        $q->bindValue(':dc', $desc_f, PDO::PARAM_STR);
        $q->bindValue(':tx', $taxa_f, PDO::PARAM_STR);
        $q->bindValue(':st', $subtotal_f, PDO::PARAM_STR);
        foreach ($binds as $k => $v) $q->bindValue($k, $v);
    } else {
        // INSERT - SOLUÇÃO UNIVERSAL
        $arq_final = empty($arquivo_nome) ? 'sem-foto.png' : $arquivo_nome;
        // ✅ Bind dinâmico: NULL se vazio, string se preenchido (funciona em MySQL 5.7 e 8.0+)
        $dp_val = !empty($data_pagamento) ? $data_pagamento : null;
        $dp_type = !empty($data_pagamento) ? PDO::PARAM_STR : PDO::PARAM_NULL;

        $q = $pdo->prepare("INSERT INTO $tabela (descricao,paciente,valor,data_vencimento,data_lancamento,data_pagamento,forma_pagamento,frequencia,obs,arquivo,usuario_lanc,usuario_pgto,multa,juros,desconto,taxa,subtotal) VALUES (:d,:p,:v,:dv,CURDATE(),:dp,:fp,:fq,:o,:a,:ul,:up,:m,:j,:dc,:tx,:st)");
        $q->bindValue(':d', $descricao);
        $q->bindValue(':p', $paciente, PDO::PARAM_INT);
        $q->bindValue(':v', $valor_base, PDO::PARAM_STR);
        $q->bindValue(':dv', $vencimento);
        $q->bindValue(':dp', $dp_val, $dp_type); // ✅ CHAVE DA SOLUÇÃO
        $q->bindValue(':fp', $forma_pagamento, PDO::PARAM_INT);
        $q->bindValue(':fq', $frequencia, PDO::PARAM_INT);
        $q->bindValue(':o', $obs);
        $q->bindValue(':a', $arq_final);
        $q->bindValue(':ul', $id_usuario_logado, PDO::PARAM_INT);
        $q->bindValue(':up', !empty($data_pagamento) ? $id_usuario_logado : null, PDO::PARAM_INT);
        $q->bindValue(':m', $multa_f, PDO::PARAM_STR);
        $q->bindValue(':j', $juros_f, PDO::PARAM_STR);
        $q->bindValue(':dc', $desc_f, PDO::PARAM_STR);
        $q->bindValue(':tx', $taxa_f, PDO::PARAM_STR);
        $q->bindValue(':st', $subtotal_f, PDO::PARAM_STR);
    }
    $q->execute();
    echo "Salvo com Sucesso";
} catch (Exception $e) {
    error_log("Erro salvar.php: " . $e->getMessage());
    echo "Erro ao salvar: " . $e->getMessage();
}
