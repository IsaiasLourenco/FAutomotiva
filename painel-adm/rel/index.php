<?php
require_once("../../conexao.php");
require_once("../../verificar.php");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Relatórios - <?php echo $nome_sistema ?></title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container py-4">
        <h3 class="mb-4"><i class="fa fa-chart-bar"></i> Relatórios Disponíveis</h3>

        <div class="row">
            <!-- ✅ Contas a Receber -->
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-hand-holding-dollar text-success fa-3x mb-3"></i>
                        <h5 class="card-title">Contas a Receber</h5>
                        <p class="card-text small text-muted">Relatório de recebimentos com filtros por data e status</p>
                        <a href="rel_receber_class.php" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fa fa-file-pdf"></i> Gerar PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- ✅ Contas a Pagar -->
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-money-bill-transfer text-danger fa-3x mb-3"></i>
                        <h5 class="card-title">Contas a Pagar</h5>
                        <p class="card-text small text-muted">Relatório de pagamentos com filtros por data e status</p>
                        <a href="rel_pagar_class.php" target="_blank" class="btn btn-outline-danger btn-sm">
                            <i class="fa fa-file-pdf"></i> Gerar PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- ✅ Clientes (Excel) -->
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-users text-primary fa-3x mb-3"></i>
                        <h5 class="card-title">Lista de Clientes</h5>
                        <p class="card-text small text-muted">Exportar todos os clientes para Excel (.xls)</p>
                        <a href="rel-clientes.php" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fa fa-file-excel"></i> Exportar Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="../../index.php?pagina=pagar" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</body>

</html>