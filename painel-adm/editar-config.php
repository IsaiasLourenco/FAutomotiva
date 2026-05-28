<?php
session_start();
require_once("../conexao.php");

if (!isset($_SESSION['id_user'])) {
    echo "Acesso negado!";
    exit;
}

// ✅ Recebe os dados
$nome_sistema     = $_POST['nome_sistema'] ?? '';
$email_sistema    = $_POST['email_sistema'] ?? '';
$telefone_sistema = $_POST['telefone_sistema'] ?? '';
$cnpj_sistema     = $_POST['cnpj'] ?? '';
$telefone_fixo    = $_POST['telefone_fixo'] ?? '';
$cep_sistema      = $_POST['cep-sistema'] ?? '';
$rua_sistema      = $_POST['rua-sistema'] ?? '';
$numero_sistema   = $_POST['numero-sistema'] ?? '';
$bairro_sistema   = $_POST['bairro-sistema'] ?? '';
$cidade_sistema   = $_POST['cidade-sistema'] ?? '';
$estado_sistema   = $_POST['estado_sistema'] ?? '';
$instagram        = $_POST['instagram'] ?? '';
$tipoRel          = $_POST['tipoRel'] ?? 'PDF';
$contatoZap       = $_POST['contatoZap'] ?? 'Sim';
$dev              = $_POST['dev'] ?? '';
$site             = $_POST['site'] ?? '';
$url_sistema      = $_POST['url_sistema'] ?? '';
$chave_pix        = $_POST['chave_pix'] ?? '';
$tipo_chave       = $_POST['tipo_chave'] ?? 'CNPJ';
$ocultar_mobile   = $_POST['ocultar_mobile'] ?? 'Não';
$alterar_acessos  = $_POST['alterar_acessos'] ?? 'Não';

// ✅ Multa e Juros Padrão
$multa_padrao       = $_POST['multa_padrao'] ?? '0,00';
$juros_padrao       = $_POST['juros_padrao'] ?? '0,00';
$multa_padrao_num   = floatval(str_replace(',', '.', str_replace('.', '', $multa_padrao)));
$juros_padrao_num   = floatval(str_replace(',', '.', str_replace('.', '', $juros_padrao)));

//Mensagens Whatsapp
$api_whatsapp       = $_POST['api_whatsapp'] ?? 'wm';
$token_whatsapp     = $_POST['token_whatsapp'] ?? '';
$instancia_whatsapp = $_POST['instancia_whatsapp'] ?? '';
$horas_confirmacao  = $_POST['horas_confirmacao'] ?? '24';

// Validação
if (empty($nome_sistema) || empty($email_sistema)) {
    echo "Preencha os campos obrigatórios!";
    exit;
}

// ✅ Upload de imagens
function processarUpload($inputName, $pastaDestino, $prefixo)
{
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK && !empty($_FILES[$inputName]['name'])) {
        $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $novo = $prefixo . '_' . uniqid() . '.' . $ext;
            $dest = $pastaDestino . '/' . $novo;
            if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $dest)) return $novo;
        }
    }
    return '';
}

$pasta_img = realpath(__DIR__ . '/../../img/');
$logotipo_nome = $pasta_img ? processarUpload('logotipo', $pasta_img, 'logo') : '';
$icone_nome = $pasta_img ? processarUpload('icone', $pasta_img, 'ico') : '';
$logo_rel_nome = $pasta_img ? processarUpload('logo_rel', $pasta_img, 'rel') : '';
$assinatura_nome = $pasta_img ? processarUpload('assinatura', $pasta_img, 'assinatura') : '';  // ✅ NOVO

try {
    $check = $pdo->query("SELECT id FROM configuracoes LIMIT 1")->fetch();

    if ($check) {
        // ✅ UPDATE
        $sql = "UPDATE configuracoes SET
            nome_sistema=:nome_sistema, email_sistema=:email_sistema,
            telefone_sistema=:telefone_sistema, cnpj_sistema=:cnpj_sistema,
            telefone_fixo=:telefone_fixo, cep_sistema=:cep_sistema,
            rua_sistema=:rua_sistema, numero_sistema=:numero_sistema,
            bairro_sistema=:bairro_sistema, cidade_sistema=:cidade_sistema,
            estado_sistema=:estado_sistema, instagram_sistema=:instagram,
            tipo_relatorio=:tipoRel, contatoZap=:contatoZap,
            desenvolvedor=:dev, site_dev=:site, url_sistema=:url_sistema,
            chave_pix=:chave_pix, tipo_chave=:tipo_chave,
            multa_padrao=:multa_padrao, juros_padrao=:juros_padrao,
            api_whatsapp=:api_whatsapp, token_whatsapp=:token_whatsapp,
            instancia_whatsapp=:instancia_whatsapp, horas_confirmacao=:horas_confirmacao,
            ocultar_mobile=:ocultar_mobile, alterar_acessos=:alterar_acessos";

        $campos_img = [];
        if ($logotipo_nome) { $sql .= ", logotipo=:logotipo"; $campos_img[':logotipo'] = $logotipo_nome; }
        if ($icone_nome) { $sql .= ", icone=:icone"; $campos_img[':icone'] = $icone_nome; }
        if ($logo_rel_nome) { $sql .= ", logo_rel=:logo_rel"; $campos_img[':logo_rel'] = $logo_rel_nome; }
        if ($assinatura_nome) { $sql .= ", assinatura=:assinatura"; $campos_img[':assinatura'] = $assinatura_nome; }  // ✅ NOVO

        $sql .= " WHERE id=:id";
        $query = $pdo->prepare($sql);

        $binds = [
            ':nome_sistema' => $nome_sistema,
            ':email_sistema' => $email_sistema,
            ':telefone_sistema' => $telefone_sistema,
            ':cnpj_sistema' => $cnpj_sistema,
            ':telefone_fixo' => $telefone_fixo,
            ':cep_sistema' => $cep_sistema,
            ':rua_sistema' => $rua_sistema,
            ':numero_sistema' => $numero_sistema,
            ':bairro_sistema' => $bairro_sistema,
            ':cidade_sistema' => $cidade_sistema,
            ':estado_sistema' => $estado_sistema,
            ':instagram' => $instagram,
            ':tipoRel' => $tipoRel,
            ':contatoZap' => $contatoZap,
            ':dev' => $dev,
            ':site' => $site,
            ':url_sistema' => $url_sistema,
            ':chave_pix' => $chave_pix,
            ':tipo_chave' => $tipo_chave,
            ':multa_padrao' => $multa_padrao_num,
            ':juros_padrao' => $juros_padrao_num,
            ':api_whatsapp' => $api_whatsapp,
            ':token_whatsapp' => $token_whatsapp,
            ':instancia_whatsapp' => $instancia_whatsapp,
            ':horas_confirmacao' => $horas_confirmacao,
            ':ocultar_mobile' => $ocultar_mobile,
            ':alterar_acessos' => $alterar_acessos,
            ':id' => $check['id']
        ];

        foreach ($binds as $k => $v) $query->bindValue($k, $v);
        foreach ($campos_img as $k => $v) $query->bindValue($k, $v);

    } else {
        // ✅ INSERT
        $query = $pdo->prepare("INSERT INTO configuracoes (
            nome_sistema, email_sistema, telefone_sistema, cnpj_sistema,
            telefone_fixo, cep_sistema, rua_sistema, numero_sistema,
            bairro_sistema, cidade_sistema, estado_sistema, instagram_sistema,
            tipo_relatorio, contatoZap, desenvolvedor, site_dev,
            url_sistema, chave_pix, tipo_chave, multa_padrao, juros_padrao,
            logotipo, icone, logo_rel, assinatura, api_whatsapp, token_whatsapp,
            instancia_whatsapp, horas_confirmacao, ocultar_mobile, alterar_acessos
        ) VALUES (
            :nome_sistema, :email_sistema, :telefone_sistema, :cnpj_sistema,
            :telefone_fixo, :cep_sistema, :rua_sistema, :numero_sistema,
            :bairro_sistema, :cidade_sistema, :estado_sistema, :instagram,
            :tipoRel, :contatoZap, :dev, :site,
            :url_sistema, :chave_pix, :tipo_chave, :multa_padrao, :juros_padrao,
            :logotipo, :icone, :logo_rel, :assinatura, :api_whatsapp, :token_whatsapp,
            :instancia_whatsapp, :horas_confirmacao, :ocultar_mobile, :alterar_acessos
        )");

        $query->bindValue(':nome_sistema', $nome_sistema);
        $query->bindValue(':email_sistema', $email_sistema);
        $query->bindValue(':telefone_sistema', $telefone_sistema);
        $query->bindValue(':cnpj_sistema', $cnpj_sistema);
        $query->bindValue(':telefone_fixo', $telefone_fixo);
        $query->bindValue(':cep_sistema', $cep_sistema);
        $query->bindValue(':rua_sistema', $rua_sistema);
        $query->bindValue(':numero_sistema', $numero_sistema);
        $query->bindValue(':bairro_sistema', $bairro_sistema);
        $query->bindValue(':cidade_sistema', $cidade_sistema);
        $query->bindValue(':estado_sistema', $estado_sistema);
        $query->bindValue(':instagram', $instagram);
        $query->bindValue(':tipoRel', $tipoRel);
        $query->bindValue(':contatoZap', $contatoZap);
        $query->bindValue(':dev', $dev);
        $query->bindValue(':site', $site);
        $query->bindValue(':url_sistema', $url_sistema);
        $query->bindValue(':chave_pix', $chave_pix);
        $query->bindValue(':tipo_chave', $tipo_chave);
        $query->bindValue(':multa_padrao', $multa_padrao_num);
        $query->bindValue(':juros_padrao', $juros_padrao_num);
        $query->bindValue(':api_whatsapp', $api_whatsapp);
        $query->bindValue(':token_whatsapp', $token_whatsapp);
        $query->bindValue(':instancia_whatsapp', $instancia_whatsapp);
        $query->bindValue(':horas_confirmacao', $horas_confirmacao);
        $query->bindValue(':ocultar_mobile', $ocultar_mobile);
        $query->bindValue(':alterar_acessos', $alterar_acessos);
        $query->bindValue(':logotipo', $logotipo_nome ?: 'logo_padrao.png');
        $query->bindValue(':icone', $icone_nome ?: 'ico_padrao.png');
        $query->bindValue(':logo_rel', $logo_rel_nome ?: 'rel_padrao.jpg');
        $query->bindValue(':assinatura', $assinatura_nome ?: 'assinatura_padrao.jpg');  // ✅ NOVO
    }

    $query->execute();
    echo "Editado com Sucesso";

} catch (Exception $e) {
    error_log("Erro editar-config: " . $e->getMessage());
    echo "Erro: " . $e->getMessage();
}
?>
