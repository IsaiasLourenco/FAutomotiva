<?php
session_start();
$tabela = 'receber';
require_once("../../../conexao.php");

// Verifica se está logado
if (!isset($_SESSION['id_user'])) {
    echo "Acesso negado!";
    exit;
}

// ✅ Recebe e sanitiza os dados do formulário
$id                 = @$_POST['id'] ?? 0;
$descricao          = $_POST['descricao'] ?? '';
$paciente           = $_POST['paciente'] ?? 0;
$valor_bruto        = $_POST['valor'] ?? '0';  // Ex: "R$ 1.234,56"
$vencimento         = $_POST['vencimento'] ?? '';
$data_pagamento     = $_POST['pagamento'] ?? '';
$forma_pagamento    = $_POST['forma_pagamento'] ?? 0;
$frequencia         = $_POST['frequencia'] ?? 0;
$obs                = $_POST['obs'] ?? '';

// ✅ Converte valor formatado "R$ 1.234,56" → 1234.56 (float para banco)
$valor_limpo = str_replace(['R$', '.', ','], ['', '', '.'], $valor_bruto);
$valor = floatval($valor_limpo);

// ✅ Validações básicas
if (empty($descricao) || empty($valor) || empty($vencimento)) {
    echo "Preencha os campos obrigatórios!";
    exit;
}

// ✅ Upload do arquivo (se enviado)
$arquivo_nome = '';
if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK && !empty($_FILES['arquivo']['name'])) {
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));

    if (in_array($extensao, $extensoes_permitidas)) {
        // Nome único: evita conflito entre registros
        $arquivo_nome = 'receber_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extensao;
        $pasta_destino = realpath(__DIR__ . '/../../images/receber/');

        if ($pasta_destino && is_dir($pasta_destino)) {
            $caminho_destino = $pasta_destino . DIRECTORY_SEPARATOR . $arquivo_nome;
            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminho_destino)) {
                echo "Erro ao salvar o arquivo!";
                exit;
            }
        } else {
            echo "Pasta de upload não encontrada!";
            exit;
        }
    } else {
        echo "Formato de arquivo não permitido!";
        exit;
    }
}

try {
    if (!empty($id) && $id != 0) {
        // ✅ UPDATE: Atualizar conta existente
        
        // Monta parte dinâmica: só atualiza data_pagamento se foi preenchida
        $extra_fields = '';
        $extra_binds = [];
        
        if (!empty($data_pagamento)) {
            $extra_fields .= ", data_pagamento = :data_pagamento";
            $extra_binds[':data_pagamento'] = $data_pagamento;
        }
        
        if (!empty($arquivo_nome)) {
            $extra_fields .= ", arquivo = :arquivo";
            $extra_binds[':arquivo'] = $arquivo_nome;
        }
        
        $query = $pdo->prepare("UPDATE $tabela SET 
            descricao = :descricao,
            paciente = :paciente,
            valor = :valor,
            data_vencimento = :data_vencimento,
            forma_pagamento = :forma_pagamento,
            frequencia = :frequencia,
            obs = :obs
            $extra_fields
            WHERE id = :id");
        
        // Bind dos campos obrigatórios
        $query->bindValue(":id", $id, PDO::PARAM_INT);
        $query->bindValue(":descricao", $descricao);
        $query->bindValue(":paciente", $paciente, PDO::PARAM_INT);
        $query->bindValue(":valor", $valor, PDO::PARAM_STR);
        $query->bindValue(":data_vencimento", $vencimento);
        $query->bindValue(":forma_pagamento", $forma_pagamento, PDO::PARAM_INT);
        $query->bindValue(":frequencia", $frequencia, PDO::PARAM_INT);
        $query->bindValue(":obs", $obs);
        
        // Bind dos campos opcionais
        foreach ($extra_binds as $key => $value) {
            $query->bindValue($key, $value);
        }
        
    } else {
        // ✅ INSERT: Cadastrar nova conta
        
        $arquivo_final = empty($arquivo_nome) ? "sem-foto.png" : $arquivo_nome;
        
        $query = $pdo->prepare("INSERT INTO $tabela (
            descricao, paciente, valor, data_vencimento, data_lancamento, 
            data_pagamento, forma_pagamento, frequencia, obs, arquivo
        ) VALUES (
            :descricao, :paciente, :valor, :data_vencimento, CURDATE(),
            :data_pagamento, :forma_pagamento, :frequencia, :obs, :arquivo
        )");
        
        $query->bindValue(":descricao", $descricao);
        $query->bindValue(":paciente", $paciente, PDO::PARAM_INT);
        $query->bindValue(":valor", $valor, PDO::PARAM_STR);
        $query->bindValue(":data_vencimento", $vencimento);
        $query->bindValue(":data_pagamento", !empty($data_pagamento) ? $data_pagamento : '');
        $query->bindValue(":forma_pagamento", $forma_pagamento, PDO::PARAM_INT);
        $query->bindValue(":frequencia", $frequencia, PDO::PARAM_INT);
        $query->bindValue(":obs", $obs);
        $query->bindValue(":arquivo", $arquivo_final);
    }

    $query->execute();
    echo "Salvo com Sucesso";
    
} catch (Exception $e) {
    error_log("Erro salvar.php (receber): " . $e->getMessage());
    echo "Erro ao salvar: " . $e->getMessage();
}
?>