<?php
date_default_timezone_set('America/Sao_Paulo');

$servidor = 'localhost';
$banco = 'odontoclinic';
$usuario = 'root';
$senha_banco = ''; // Renomeado para evitar conflito com $senha de formulários

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$banco;charset=utf8mb4", $usuario, $senha_banco);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Erro ao conectar ao banco de dados!<br>" . $e->getMessage();
    exit;
}

// ============================================================================
// CARREGA CONFIGURAÇÕES DO BANCO (se a tabela existir e tiver registros)
// ============================================================================
try {
    // Verifica se a tabela existe antes de consultar
    $check_table = $pdo->query("SHOW TABLES LIKE 'configuracoes'")->fetch();

    if ($check_table) {
        $config = $pdo->query("SELECT * FROM configuracoes LIMIT 1")->fetch(PDO::FETCH_ASSOC);

        if ($config) {
            // Cria as variáveis a partir dos campos do banco
            extract($config);
        }
    }
} catch (Exception $e) {
    // Se der erro, usa os valores padrão abaixo
}

// ============================================================================
// VALORES PADRÃO (fallback se não houver configurações no banco)
// ============================================================================
if (!isset($nome_sistema)) {
    $nome_sistema = 'FG Odontologia e Estética';
    $email_sistema = 'o.implantare@gmail.com';
    $cnpj_sistema = '45.057.703/0001-94';
    $telefone_sistema = '(19) 99574-5466';
    $telefone_fixo = '(19) 97111-0538';
    $cep_sistema = '13843-184';
    $rua_sistema = 'Mocóca';
    $numero_sistema = '880';
    $bairro_sistema = 'Loteamento Parque Itacolomy';
    $cidade_sistema = 'Mogi Guaçu';
    $estado_sistema = 'SP';
    $instagram_sistema = 'https://www.instagram.com/fgodontologiaestetica/';
    $tipo_relatorio = 'PDF';
    $contatoZap = 'Sim';
    $desenvolvedor = 'Vetor256.';
    $site_dev = 'https://vetor256.com/';
    $url_sistema = 'http://localhost/odontoclinic/';
    $chave_pix = '45057703000194';
    $tipo_chave = 'CNPJ';
    $logotipo = 'logo_padrao.png';
    $icone = 'ico_padrao.png';
    $logo_rel = 'rel_padrao.jpg';
    $multa_padrao = '0,00';
    $juros_padrao = '0,00';
    $marca_dagua_rel = 'rel_padrao.jpg';
}

// | ENDEREÇO FORMATADO
$endereco_sistema = trim(
    $rua_sistema . ', ' .
    $numero_sistema . ' - ' .
    $bairro_sistema . ' - ' .
    $cidade_sistema . '/' .
    $estado_sistema
);
?>