<?php
require_once("conexao.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_sistema ?></title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Icone -->
    <link rel="shortcut icon" href="img/icone.ico" type="image/x-icon">
    <!-- CSS -->
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="container login-container d-flex justify-content-center align-items-center">
        <div class="card card-login shadow" style="max-width: 400px; width: 100%;">
            <div class="text-center">
                <img src="img/Logo.png" alt="Logotipo" class="logo-img">
            </div>
            <form method="POST" action="autenticar.php">
                <!-- EMAIL -->
                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" name="email" class="form-control" placeholder="Seu email" required>
                </div>
                <!-- SENHA -->
                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" name="senha" id="senha" class="form-control" placeholder="Sua senha" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="toggleSenha()">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <!-- BOTÃO LOGIN -->
                <button type="submit" class="btn btn-login text-white w-100 mb-2">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>
            <!-- RECUPERAR -->
            <button data-bs-toggle="modal" data-bs-target="#modalRecuperar"
                class="btn btn-recuperar text-white w-100 mb-2">
                <i class="bi bi-key"></i> Recuperar senha
            </button>
            <!-- VOLTAR -->
            <a href="index.html" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-left"></i> Voltar para o site
            </a>
        </div>
    </div>
    <!-- MODAL -->
    <div class="modal fade" id="modalRecuperar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <form method="post" id="form-recuperar">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">
                            <i class="bi bi-key"></i> Recuperar senha
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" name="email" id="email-recuperar"
                                class="form-control" placeholder="Digite seu email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-send"></i> Enviar recuperação
                        </button>
                        <div id="mensagem-recuperar" class="mt-3 text-center"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSenha() {
            const input = document.getElementById("senha");
            input.type = input.type === "password" ? "text" : "password";
        }

        $("#form-recuperar").submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: 'recuperar-senha.php',
                method: 'POST',
                data: formData,
                success: function(resposta) {
                    // ✅ IDs CORRETOS:
                    $('#mensagem-recuperar').html(resposta);

                    // ✅ Estilo para localhost (só visual, não quebra em produção)
                    if (resposta.includes('http://localhost')) {
                        $('#mensagem-recuperar')
                            .css('background', '#e7f1ff')
                            .css('padding', '15px')
                            .css('border-radius', '5px')
                            .css('margin-top', '10px');
                    }
                },
                error: function() {
                    $('#mensagem-recuperar').html('<span class="text-danger">Erro ao processar solicitação.</span>');
                }
            });
        });
    </script>
</body>

</html>
