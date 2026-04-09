<?php
require_once("conexao.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_sistema ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/icone.ico" type="image/x-icon">
</head>
<body>
    <div class="login">
        <img src="img/Logo.png" alt="Logotipo" class="logo-img">
        <div class="form">
            <form method="POST" action="autenticar.php" class="registro">
                <input type="email" name="email" placeholder="Usuário:" class="userLogin" required>
                <input type="password" name="senha" placeholder="Senha:" required>
                <button type="submit">Login</button>
            </form>
            <p class="recuperar">
                <a href="#" data-bs-toggle="modal" data-bs-target="#modalRecuperar">Recuperar a Senha</a>
            </p>
        </div>
    </div>

    <!-- Modal Recupera Senha-->
    <div class="modal fade" id="modalRecuperar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content form">
                <form method="post" id="form-recuperar">
                    <div class="modal-body">
                        <input placeholder="Digite seu Email" class="form-control" type="email" name="email" id="email-recuperar" required>
                        <button type="submit" class="btn btn-primary text-black mt-3 w-100">Recuperar</button>
                    </div>
                    <div id="mensagem-recuperar" class="px-3 pb-3"></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $("#form-recuperar").submit(function(event) {
            event.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: "recuperar-senha.php",
                type: "POST",
                data: formData,
                dataType: "json",
                cache: false,
                contentType: false,
                processData: false,
                success: function(resposta) {
                    $("#mensagem-recuperar").removeClass().text('');
                    if (resposta.status === "success") {
                        let email = $("#email-recuperar").val();
                        $("#email-recuperar").val('');
                        $("#mensagem-recuperar")
                            .addClass("text-success")
                            .html(`<p>Sua requisição foi enviada para <strong>${email}</strong>.</p>
                                   <p>Por favor, confira sua caixa de entrada (e spam) e use o link para alterar sua senha.</p>
                                   <p>⚠️ O link expira em 2 horas.</p>`);
                    } else {
                        $("#mensagem-recuperar").addClass("text-danger").text(resposta.message);
                    }
                },
                error: function() {
                    $("#mensagem-recuperar").addClass("text-danger").text("Erro ao processar a solicitação.");
                }
            });
        });
    </script>
</body>
</html>