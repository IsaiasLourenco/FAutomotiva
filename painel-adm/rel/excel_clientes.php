<?php
require_once("../../conexao.php");
require_once("../verificar.php"); // ✅ Só usuários logados podem exportar

// ✅ Cabeçalhos para forçar download como Excel
$arquivo = "clientes_" . date('Y-m-d') . ".xls";
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $arquivo . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

// ✅ BOM para Excel reconhecer UTF-8 (acentos e ç)
echo "\xEF\xBB\xBF";

// ✅ Início da tabela HTML (Excel lê como planilha)
$dadosXls = "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; font-family: Arial, sans-serif;'>";

// ✅ Cabeçalho com estilo
$dadosXls .= "<tr style='background-color:#28a745; color:white; font-weight:bold; text-align:center;'>";
$dadosXls .= "<th>ID</th><th>Nome</th><th>CPF</th><th>Telefone</th><th>Email</th><th>Endereço</th><th>Bairro</th><th>Cidade/UF</th><th>CEP</th>";
$dadosXls .= "</tr>";

// ✅ Busca todos os clientes ordenados por nome
$query = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC");
$res = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($res as $row) {
    // ✅ Monta endereço completo
    $endereco = trim(($row['rua'] ?? '') . ', ' . ($row['numero'] ?? ''));
    if (empty($endereco) || $endereco == ', ') {
        $endereco = '-';
    }

    // ✅ Monta cidade/estado
    $cidade_uf = trim(($row['cidade'] ?? '') . '/' . ($row['estado'] ?? ''));
    if (empty($cidade_uf) || $cidade_uf == '/') {
        $cidade_uf = '-';
    }

    // ✅ Formata CPF (remove máscara se já tiver, para export limpo)
    $cpf_export = $row['cpf'] ?? '';

    $dadosXls .= "<tr style='text-align:left;'>";
    $dadosXls .= "<td style='text-align:center;'>" . htmlspecialchars($row['id'] ?? '') . "</td>";
    $dadosXls .= "<td><strong>" . htmlspecialchars($row['nome'] ?? '') . "</strong></td>";
    $dadosXls .= "<td style='text-align:center;'>" . htmlspecialchars($cpf_export) . "</td>";
    $dadosXls .= "<td>" . htmlspecialchars($row['telefone'] ?? '') . "</td>";
    $dadosXls .= "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
    $dadosXls .= "<td>" . htmlspecialchars($endereco) . "</td>";
    $dadosXls .= "<td>" . htmlspecialchars($row['bairro'] ?? '') . "</td>";
    $dadosXls .= "<td>" . htmlspecialchars($cidade_uf) . "</td>";
    $dadosXls .= "<td style='text-align:center;'>" . htmlspecialchars($row['cep'] ?? '') . "</td>";
    $dadosXls .= "</tr>";
}

$dadosXls .= "</table>";

echo $dadosXls;
exit;
?>
