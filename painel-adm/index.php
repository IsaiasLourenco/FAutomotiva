    <?php
    session_start();
    require_once("../conexao.php");
    require_once("verificar.php");

    $data_atual = date('Y-m-d');
    $mes_atual = date('m');
    $ano_atual = date('Y');

    // ✅ Início e fim do mês atual (considera ano bissexto)
    $data_inicio_mes = $ano_atual . '-' . $mes_atual . '-01';
    $data_final_mes = date('Y-m-t', strtotime($data_inicio_mes));

    // ✅ Início e fim do ano atual
    $data_inicio_ano = $ano_atual . '-01-01';
    $data_final_ano = $ano_atual . '-12-31';

    $data_ontem = date('Y-m-d', strtotime("-1 days", strtotime($data_atual)));
    $data_amanha = date('Y-m-d', strtotime("+1 days", strtotime($data_atual)));

    // Valores padrão: Admin vê tudo
    $home = '';
    $configuracoes = '';
    //Pessoas
    $usuarios = '';
    $pacientes = '';
    $fornecedores = '';
    $menu_pessoas = '';
    //Cadastros
    $grupo_acessos = '';
    $acessos = '';
    $cargos = '';
    $forma_pagamento = '';
    $frequencias = '';
    $menu_cadastros = '';
    //Financeiro
    $pagar = '';
    $receber = '';
    $relfin = '';
    $menu_financeiro = '';

    $pag_inicial = 'home';

    $id_cargo_user = $_SESSION['id_cargo_user'];
    $query_cargo = $pdo->prepare("SELECT nome FROM cargos WHERE id = :id_cargo_user LIMIT 1");
    $query_cargo->bindValue(":id_cargo_user", "$id_cargo_user", PDO::PARAM_INT);
    $query_cargo->execute();
    $res_cargo = $query_cargo->fetch(PDO::FETCH_ASSOC);
    $nome_cargo_usuario = $res_cargo['nome'];

    $pag_inicial = 'home';
    if ($nome_cargo_usuario != 'Administrador') {
        require_once("verificar_permissoes.php");
    }

    if (@$_GET['pagina'] != "") {
        $pagina = @$_GET['pagina'];
    } else {
        $pagina = $pag_inicial;
    }

    $id_user = $_SESSION['id_user'];

    $query = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
    $query->bindValue(":id", $id_user);
    $query->execute();
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $linhas = count($res);
    if ($linhas > 0) {
        $nome_usuario = $res[0]['nome'];
        $email_usuario = $res[0]['email'];
        $senha_usuario = $res[0]['senha'];
        $id_cargo_usuario = $res[0]['cargo'];
        $telefone_usuario = $res[0]['telefone'];
        $cpf_usuario = $res[0]['cpf'];
        $cep_usuario = $res[0]['cep'];
        $rua_usuario = $res[0]['rua'];
        $numero_usuario = $res[0]['numero'];
        $bairro_usuario = $res[0]['bairro'];
        $cidade_usuario = $res[0]['cidade'];
        $estado_usuario = $res[0]['estado'];
        $foto_usuario = $res[0]['foto'];

        $cargo = $pdo->prepare("SELECT * FROM cargos WHERE id = :id_cargo");
        $cargo->bindValue(":id_cargo", $id_cargo_usuario);
        $cargo->execute();
        $res_cargo = $cargo->fetchAll(PDO::FETCH_ASSOC);
        $nome_cargo_usuario = $res_cargo[0]['nome'];
    }

    ?>
    <!DOCTYPE HTML>
    <html>

    <head>
        <title><?php echo $nome_sistema ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" href="../img/icone.ico" type="image/x-icon">

        <script type="application/x-javascript">
            addEventListener("load", function() {
                setTimeout(hideURLbar, 0);
            }, false);

            function
            hideURLbar() {
                window.scrollTo(0, 1);
            }
        </script>

        <!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.css" rel='stylesheet' type='text/css' />

        <!-- Custom CSS -->
        <link href="css/style.css" rel='stylesheet' type='text/css' />

        <!-- font-awesome icons CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <!-- //font-awesome icons CSS-->

        <!-- side nav css file -->
        <link href='css/SidebarNav.min.css' media='all' rel='stylesheet' type='text/css' />
        <!-- //side nav css file -->

        <!-- js-->
        <script src="js/jquery-1.11.1.min.js"></script>
        <script src="js/modernizr.custom.js"></script>

        <!--webfonts-->
        <link href="//fonts.googleapis.com/css?family=PT+Sans:400,400i,700,700i&amp;subset=cyrillic,cyrillic-ext,latin-ext" rel="stylesheet">
        <!--//webfonts-->

        <!-- chart -->
        <script src="js/Chart.js"></script>
        <!-- //chart -->

        <!-- Metis Menu -->
        <script src="js/metisMenu.min.js"></script>
        <script src="js/custom.js"></script>
        <link href="css/custom.css" rel="stylesheet">
        <!--//Metis Menu -->
        <style>
            #chartdiv {
                width: 100%;
                height: 295px;
            }
        </style>
        <!--pie-chart --><!-- index page sales reviews visitors pie chart -->
        <script src="js/pie-chart.js" type="text/javascript"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#demo-pie-1').pieChart({
                    barColor: '#2dde98',
                    trackColor: '#eee',
                    lineCap: 'round',
                    lineWidth: 8,
                    onStep: function(from, to, percent) {
                        $(this.element).find('.pie-value').text(Math.round(percent) + '%');
                    }
                });

                $('#demo-pie-2').pieChart({
                    barColor: '#8e43e7',
                    trackColor: '#eee',
                    lineCap: 'butt',
                    lineWidth: 8,
                    onStep: function(from, to, percent) {
                        $(this.element).find('.pie-value').text(Math.round(percent) + '%');
                    }
                });

                $('#demo-pie-3').pieChart({
                    barColor: '#ffc168',
                    trackColor: '#eee',
                    lineCap: 'square',
                    lineWidth: 8,
                    onStep: function(from, to, percent) {
                        $(this.element).find('.pie-value').text(Math.round(percent) + '%');
                    }
                });

            });
        </script>
        <!-- //pie-chart --><!-- index page sales reviews visitors pie chart -->

    </head>

    <body class="cbp-spmenu-push">
        <div class="main-content">
            <div class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-left" id="cbp-spmenu-s1">
                <!--left-fixed -navigation-->
                <aside class="sidebar-left" style="overflow: scroll; height:100%; scrollbar-width: thin;">
                    <nav class="navbar navbar-inverse">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".collapse" aria-expanded="false">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                            <h1>
                                <a class="navbar-brand" href="index.php">
                                    <span class="fa fa-tooth"></span> Sistema
                                    <span class="dashboard_text">
                                        <?php echo $nome_sistema ?>
                                    </span>
                                </a>
                            </h1>
                        </div>
                        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                            <ul class="sidebar-menu">
                                <li class="header-nav-title">MENU NAVEGAÇÃO</li>
                                <li class="treeview <?php echo $home ?>">
                                    <a href="index.php">
                                        <i class="fa fa-home"></i> <span>Home</span>
                                    </a>
                                </li>
                                <li class="treeview <?php echo $menu_pessoas ?>">
                                    <a href="#">
                                        <i class="fa fa-users"></i>
                                        <span>Pessoas</span>
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </a>
                                    <ul class="treeview-menu">
                                        <?php if ($usuarios != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=usuarios"><i class="fa fa-angle-right"></i> Usuários</a></li>
                                        <?php } ?>
                                        <?php if ($pacientes != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=pacientes"><i class="fa fa-angle-right"></i> Pacientes</a></li>
                                        <?php } ?>
                                        <?php if ($fornecedores != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=fornecedores"><i class="fa fa-angle-right"></i> Fornecedores</a></li>
                                        <?php } ?>
                                    </ul>
                                </li>
                                <li class="treeview <?php echo $menu_cadastros ?>">
                                    <a href="#">
                                        <i class="fa-solid fa-folder-plus"></i>
                                        <span>Cadastros</span>
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </a>
                                    <ul class="treeview-menu">
                                        <?php if ($forma_pagamento != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=forma_pagamento"><i class="fa fa-angle-right"></i> Formas Pagto</a></li>
                                        <?php } ?>
                                        <?php if ($frequencias != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=frequencias"><i class="fa fa-angle-right"></i> Frequências</a></li>
                                        <?php } ?>
                                        <?php if ($cargos != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=cargos"><i class="fa fa-angle-right"></i> Cargos</a></li>
                                        <?php } ?>
                                        <?php if ($grupo_acessos != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=grupo_acessos"><i class="fa fa-angle-right"></i> Grupos</a></li>
                                        <?php } ?>
                                        <?php if ($acessos != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=acessos"><i class="fa fa-angle-right"></i> Acessos</a></li>
                                        <?php } ?>
                                    </ul>
                                </li>
                                <li class="treeview <?php echo $menu_financeiro ?>">
                                    <a href="#">
                                        <i class="fa-solid fa-dollar-sign"></i>
                                        <span>Financeiro</span>
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </a>
                                    <ul class="treeview-menu">
                                        <?php if ($receber != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=receber"><i class="fa fa-angle-right"></i> Receber</a></li>
                                        <?php } ?>
                                        <?php if ($pagar != 'ocultar') { ?>
                                            <li><a href="index.php?pagina=pagar"><i class="fa fa-angle-right"></i> Pagar</a></li>
                                        <?php } ?>
                                        <?php if ($relfin != 'ocultar') { ?>
                                            <li>
                                                <a href="#" data-toggle="modal" data-target="#modalRelFin">
                                                    <i class="fa fa-angle-right"></i> Relatório Financeiro
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <!-- /.navbar-collapse -->
                    </nav>
                </aside>
            </div>
            <!--left-fixed -navigation-->

            <!-- header-starts -->
            <div class="sticky-header header-section ">
                <div class="header-left">
                    <!--toggle button start-->
                    <button title="Esconder/Mostrar Menu Lateral" id="showLeftPush" data-toggle="collapse" data-target=".collapse">
                        <i class="fa fa-bars"></i>
                    </button>
                    <!--toggle button end-->
                    <?php
                    $query = $pdo->query("SELECT * FROM receber WHERE data_pagamento IS NULL ORDER BY data_vencimento");
                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                    $linhas = count($res);
                    $linhasF = str_pad($linhas, 2, '0', STR_PAD_LEFT);
                    ?>
                    <div class="profile_details_left"><!--notifications of menu start -->
                        <ul class="nofitications-dropdown">
                            <li class="dropdown head-dpdn">
                                <a href="#" title="Contas à receber" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-money-bills btn-receber"></i>
                                    <span class="badge"><?php echo $linhas; ?></span>
                                </a>
                                <ul class="dropdown-menu" style="background: beige;">
                                    <li>
                                        <div class="notification_header">
                                            <h3>Você possui <strong><?php echo $linhasF; ?></strong> conta(s) à receber</h3>
                                        </div>
                                    </li>
                                    <?php
                                    $query = $pdo->query("SELECT * FROM receber WHERE data_pagamento IS NULL ORDER BY data_vencimento ASC LIMIT 5");
                                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                    for ($i = 0; $i < count($res); $i++) {
                                    ?>
                                        <li>
                                            <a href="#">
                                                <div class="notification_desc">
                                                    <p>
                                                        <span class="text-danger" style="color: red !important; font-weight: bold;">
                                                            <?php echo 'R$ ' . number_format($res[$i]['valor'], 2, ',', '.'); ?>
                                                        </span> |
                                                        <?php echo $res[$i]['descricao']; ?>
                                                        <!-- <?php echo date('d/m/Y', strtotime($res[$i]['data_vencimento'])); ?> -->
                                                    </p>
                                                </div>
                                                <div class="clearfix"></div>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <li>
                                        <div class="notification_bottom">
                                            <a href="index.php?pagina=receber">Ver todas as contas</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <div class="clearfix"> </div>
                    </div>

                    <div class="profile_details_left"><!--notifications of menu start -->
                        <?php
                        $query = $pdo->query("SELECT * FROM pagar WHERE data_pagamento IS NULL ORDER BY data_vencimento ASC");
                        $resP = $query->fetchAll(PDO::FETCH_ASSOC);
                        $linhasP = count($resP);
                        $linhasPF = str_pad($linhasP, 2, '0', STR_PAD_LEFT);
                        ?>
                        <ul class="nofitications-dropdown">
                            <li class="dropdown head-dpdn">
                                <a href="#" title="Contas à pagar" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-money-bills btn-pagar"></i>
                                    <span class="badge" style="background:red"><?php echo $linhasP; ?></span>
                                </a>
                                <ul class="dropdown-menu" style="background: beige;">
                                    <li>
                                        <div class="notification_header">
                                            <h3>Você possui <strong><?php echo $linhasPF; ?></strong> conta(s) à pagar</h3>
                                        </div>
                                    </li>
                                    <?php
                                    $query = $pdo->query("SELECT * FROM pagar WHERE data_pagamento IS NULL ORDER BY data_vencimento ASC LIMIT 5");
                                    $resP = $query->fetchAll(PDO::FETCH_ASSOC);
                                    for ($i = 0; $i < count($resP); $i++) {
                                    ?>
                                        <li>
                                            <a href="#">
                                                <div class="notification_desc">
                                                    <p>
                                                        <span class="text-danger" style="color: red !important; font-weight: bold;">
                                                            <?php echo 'R$ ' . number_format($resP[$i]['valor'], 2, ',', '.'); ?>
                                                        </span> |
                                                        <?php echo $resP[$i]['descricao']; ?>
                                                    </p>
                                                </div>
                                                <div class="clearfix"></div>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <li>
                                        <div class="notification_bottom">
                                            <a href="index.php?pagina=pagar">Ver todas as contas</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <div class="clearfix"> </div>
                    </div>

                </div>



                <div class="header-right">

                    <div class="profile_details">
                        <ul>
                            <li class="dropdown profile_details_drop">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <div class="profile_img">
                                        <span class="prfil-img"><img src="images/perfil/<?php echo $foto_usuario ?>" alt="" width="50px" height="50px"> </span>
                                        <div class="user-name esc">
                                            <p><?= $nome_usuario ?></p>
                                            <span><?= $nome_cargo_usuario ?></span>
                                        </div>
                                        <i class="fa fa-angle-down lnr"></i>
                                        <i class="fa fa-angle-up lnr"></i>
                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                                <ul class="dropdown-menu drp-mnu">
                                    <?php if ($configuracoes != 'ocultar') { ?>
                                        <li> <a href="" data-toggle="modal" data-target="#modalConfig"><i class="fa fa-cog"></i> Configurações</a> </li>
                                    <?php } ?>
                                    <?php if ($configuracoes != 'ocultar') { ?>
                                        <li> <a href="" data-toggle="modal" data-target="#modalPerfil"><i class="fa fa-user"></i> Perfil</a> </li>
                                    <?php } ?>
                                    <li> <a href="logout.php"><i class="fa fa-sign-out"></i> Sair</a> </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="clearfix"> </div>
                </div>
                <div class="clearfix"> </div>
            </div>
            <!-- //header-ends -->

            <!-- main content start-->
            <div id="page-wrapper">
                <?php
                if (@$_GET['pagina'] != "") {
                    $pagina = @$_GET['pagina'];
                } else {
                    $pagina = $pag_inicial;  // ← Usa o valor calculado pelo verificar_permissoes.php ✅
                }
                $arquivo = __DIR__ . '/paginas/' . $pagina . '.php';

                echo "<!-- DEBUG: Buscando: $arquivo -->";

                if (file_exists($arquivo)) {
                    echo "<!-- ✅ Arquivo existe -->";
                    require_once $arquivo;
                } else {
                    echo "<!-- ❌ NÃO existe. Arquivos na pasta: -->";
                    $pasta = __DIR__ . '/paginas/';
                    foreach (glob($pasta . '*') as $f) {
                        echo "<!-- • " . basename($f) . " -->";
                    }
                }
                ?>
            </div>

            <!-- main content start-->
            <!-- <div id="page-wrapper">
                <?php
                require_once('paginas/' . $pagina . '.php');
                ?>
            </div> -->
            <!--footer-->
            <div class="footer rodape">
                <?php require_once("footer.php"); ?>
            </div>
        </div>

        <!-- new added graphs chart js-->

        <script src="js/Chart.bundle.js"></script>
        <script src="js/utils.js"></script>

        <!-- Classie --><!-- for toggle left push menu script -->
        <script src="js/classie.js"></script>
        <script>
            var menuLeft = document.getElementById('cbp-spmenu-s1'),
                showLeftPush = document.getElementById('showLeftPush'),
                body = document.body;

            showLeftPush.onclick = function() {
                classie.toggle(this, 'active');
                classie.toggle(body, 'cbp-spmenu-push-toright');
                classie.toggle(menuLeft, 'cbp-spmenu-open');
                disableOther('showLeftPush');
            };

            function disableOther(button) {
                if (button !== 'showLeftPush') {
                    classie.toggle(showLeftPush, 'disabled');
                }
            }
        </script>
        <!-- //Classie --><!-- //for toggle left push menu script -->

        <!--scrolling js-->
        <script src="js/jquery.nicescroll.js"></script>
        <script src="js/scripts.js"></script>
        <!--//scrolling js-->

        <!-- side nav js -->
        <script src='js/SidebarNav.min.js' type='text/javascript'></script>
        <script>
            $('.sidebar-menu').SidebarNav()
        </script>
        <!-- //side nav js -->

        <!-- Bootstrap Core JavaScript -->
        <script src="js/bootstrap.js"> </script>
        <!-- //Bootstrap Core JavaScript -->

        <!-- Ajax para funcionar Mascaras JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
        <!-- Mascaras JS -->
        <script type="text/javascript" src="../js/mascaras.js"></script>
        <script type="text/javascript" src="../js/buscaCepModal.js"></script>
        <script type="text/javascript" src="../js/validarCNPJ.js"></script>
        <script type="text/javascript" src="../js/validarCPF.js"></script>


        <!-- Datatables -->
        <link href="https://cdn.datatables.net/v/dt/dt-2.3.7/datatables.min.css" rel="stylesheet"
            integrity="sha384-wCnlGUpaekN+Mtc+qIoipdqIqe2dvC7hWyzVg8wajZ1sxKnVTbnyBd7pyx7JT0Su" crossorigin="anonymous">
        <script src="https://cdn.datatables.net/v/dt/dt-2.3.7/datatables.min.js"
            integrity="sha384-aQ8I1X2x8U0AR8D7C4Ah0OvZlwMslQdN5YDAQBA56jXrrhcECijs/i7H+5DDrlV1" crossorigin="anonymous"></script>


    </body>



    </html>

    <!-- Modal Perfil-->
    <div class="modal fade" id="modalPerfil" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Alterar Dados</h4>
                    <button id="btn-fechar-perfil" type="button" class="close mg-t--20" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-perfil">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nome">Nome</label>
                                <input type="text" class="form-control" id="nome-perfil" name="nome" value="<?php echo $nome_usuario ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email">E-mail</label>
                                <input type="email" class="form-control" id="email-perfil" name="email" value="<?php echo $email_usuario ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="cpf">CPF</label>
                                <input type="text" class="form-control cpf" id="cpf-perfil" name="cpf" value="<?php echo $cpf_usuario ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telefone">Telefone</label>
                                <input type="text" class="form-control" id="telefone-perfil" name="telefone" value="<?php echo $telefone_usuario ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <label for="cep">CEP</label>
                                <input type="text" class="form-control" id="cep-perfil-config" name="cep" value="<?php echo $cep_usuario ?>" required>
                            </div>
                            <div class="col-md-5">
                                <label for="rua">Rua</label>
                                <input type="text" class="form-control" id="rua-perfil" name="rua" value="<?php echo $rua_usuario ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="numero">Número</label>
                                <input type="text" class="form-control" id="numero-perfil" name="numero" value="<?php echo $numero_usuario ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <label for="bairro">Bairro</label>
                                <input type="text" class="form-control" id="bairro-perfil" name="bairro" value="<?php echo $bairro_usuario ?>" readonly>
                            </div>
                            <div class="col-md-5">
                                <label for="cidade">Cidade</label>
                                <input type="text" class="form-control" id="cidade-perfil" name="cidade" value="<?php echo $cidade_usuario ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="estado">Estado</label>
                                <input type="text" class="form-control" id="estado-perfil" name="estado" value="<?php echo $estado_usuario ?>" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="senha">Senha</label>
                                <input type="password" class="form-control" id="senha-perfil" name="senha" value="<?php echo $senha_usuario ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="conf-senha">Confirmar Senha</label>
                                <input type="password" class="form-control" id="conf-senha-perfil" name="conf-senha">
                            </div>
                            <div class="col-md-5">
                                <label for="nivel">Nível</label>
                                <?php if ($nome_cargo_usuario  == 'Administrador') { ?>
                                    <select class="form-control" name="nivel" id="nivel">
                                        <?php
                                        $query = $pdo->query("SELECT * FROM cargos ORDER BY nome asc");
                                        $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                        $total_reg = @count($res);

                                        if ($total_reg > 0) {
                                            for ($i = 0; $i < $total_reg; $i++) {
                                                echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
                                            }
                                        } else {
                                            echo '<option value="0">Cadastre um Cargo</option>';
                                        }
                                        ?>
                                    </select>
                                <?php } else { ?>
                                    <input type="text" class="form-control" id="nivel-perfil"
                                        name="nivel" value="<?= $nome_cargo_usuario  ?>" readonly>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="ativo">Ativo</label>
                                <select class="form-control" name="ativo" id="ativo">
                                    <option value="Sim" selected>Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="foto">Foto</label>
                                <input type="file" class="form-control" id="foto-perfil" name="foto" onchange="carregarImgPerfil()">
                            </div>
                            <div class="col-md-6">
                                <img src="./images/perfil/<?php echo $foto_usuario ?>" alt="Foto do usuário" style="width: 80px;" id="target-usu">
                            </div>
                            <input type="hidden" name="id-usuario" value="<?php echo $id_usuario ?>">
                        </div>
                    </div>
                    <div id="msg-perfil" class="centro"></div>
                    <div class="modal-footer centro">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Fim Modal Perfil-->

    <!-- Modal Config-->
    <div class="modal fade" id="modalConfig" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Editar Configurações</h4>
                    <button id="btn-fechar-config" type="button" class="close mg-t--20" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-config">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="nome_sistema">Nome do Sistema</label>
                                <input type="text" class="form-control" id="nome_sistema" name="nome_sistema" value="<?php echo $nome_sistema ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="email_sistema">E-mail do Sistema</label>
                                <input type="email" class="form-control" id="email_sistema" name="email_sistema" value="<?php echo $email_sistema ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="telefone_sistema">Telefone do Sistema</label>
                                <input type="text" class="form-control" id="telefone_sistema" name="telefone_sistema" value="<?php echo $telefone_sistema ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="cnpj_sistema">CNPJ</label>
                                <input type="text" class="form-control cnpj" name="cnpj" value="<?php echo $cnpj_sistema ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="telefone_fixo">Telefone Fixo</label>
                                <input type="text" class="form-control" id="telefone_fixo" name="telefone_fixo" value="<?php echo $telefone_fixo ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="cep-sistema">CEP</label>
                                <input type="text" class="form-control" id="cep-sistema" name="cep-sistema" value="<?php echo $cep_sistema ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <label for="rua-sistema">Rua</label>
                                <input type="text" class="form-control" id="rua-sistema" name="rua-sistema" value="<?php echo $rua_sistema ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="numero-sistema">Número</label>
                                <input type="text" class="form-control" id="numero-sistema" name="numero-sistema" value="<?php echo $numero_sistema ?>" required>
                            </div>
                            <div class="col-md-5">
                                <label for="bairro-sistema">Bairro</label>
                                <input type="text" class="form-control" id="bairro-sistema" name="bairro-sistema" value="<?php echo $bairro_sistema ?>" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <label for="cidade">Cidade</label>
                                <input type="text" class="form-control" id="cidade-sistema" name="cidade-sistema" value="<?php echo $cidade_sistema ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="estado-sistema">Estado</label>
                                <input type="text" class="form-control" id="estado-sistema" name="estado_sistema" value="<?php echo $estado_sistema ?>" readonly>
                            </div>
                            <div class="col-md-5">
                                <label for="instagram">Instagram</label>
                                <input type="text" class="form-control" id="instagram" name="instagram" value="<?php echo $instagram_sistema ?>">
                            </div>
                        </div>

                        <!-- ✅ NOVOS CAMPOS: Multa e Juros Padrão -->
                        <div class="row border-top pt-3 mt-3">
                            <div class="col-md-6">
                                <label for="multa_padrao">Multa Padrão (%)</label>
                                <input type="text" class="form-control moeda" id="multa_padrao" name="multa_padrao"
                                    value="<?php echo isset($multa_padrao) ? number_format($multa_padrao, 2, ',', '.') : '0,00'; ?>"
                                    placeholder="0,00" maxlength="5">
                                <small class="text-muted">Ex: 2,00 para 2%</small>
                            </div>
                            <div class="col-md-6">
                                <label for="juros_padrao">Juros Padrão (% ao mês)</label>
                                <input type="text" class="form-control moeda" id="juros_padrao" name="juros_padrao"
                                    value="<?php echo isset($juros_padrao) ? number_format($juros_padrao, 2, ',', '.') : '0,00'; ?>"
                                    placeholder="0,00" maxlength="5">
                                <small class="text-muted">Ex: 0,33 para 0,33% a.m.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <label for="tipoRel">Tipo Relatório</label>
                                <select class="form-control" name="tipoRel">
                                    <option value="PDF" <?php if ($tipo_relatorio == 'PDF') { ?> selected <?php } ?>>PDF</option>
                                    <option value="HTML" <?php if ($tipo_relatorio == 'HTML') { ?> selected <?php } ?>>HTML</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="pedidos">Contato Whatsapp</label>
                                <select class="form-control" name="contatoZap">
                                    <option value="Sim" <?php if ($contatoZap == 'Sim') { ?> selected <?php } ?>>Sim</option>
                                    <option value="Não" <?php if ($contatoZap == 'Não') { ?> selected <?php } ?>>Não</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="dev">Desenvolvedor</label>
                                <input type="text" class="form-control" id="dev" name="dev" value="<?php echo $desenvolvedor ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="site">Site</label>
                                <input type="text" class="form-control" id="site" name="site" value="<?php echo $site_dev ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="url_sistema">URL para Relatório</label>
                                <input type="text" class="form-control" id="url_sistema" name="url_sistema"
                                    value="<?php echo $url_sistema ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="chave_pix">Chave PIX</label>
                                <input type="text" class="form-control" id="chave_pix" name="chave_pix"
                                    value="<?php echo $chave_pix ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="tipo_chave">Tipo Chave</label>
                                <select class="form-control" name="tipo_chave">
                                    <option value="CNPJ" <?php if ($tipo_chave == 'CNPJ') { ?> selected <?php } ?>>CNPJ</option>
                                    <option value="CPF" <?php if ($tipo_chave == 'CPF') { ?> selected <?php } ?>>CPF</option>
                                    <option value="Email" <?php if ($tipo_chave == 'Email') { ?> selected <?php } ?>>Email</option>
                                    <option value="Telefone" <?php if ($tipo_chave == 'Telefone') { ?> selected <?php } ?>>Telefone</option>
                                    <option value="Codigo" <?php if ($tipo_chave == 'Codigo') { ?> selected <?php } ?>>Codigo</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="logotipo">Logotipo(*.png)</label>
                                <input type="file" class="form-control" id="logotipo" name="logotipo" onchange="carregarImgLogotipo()">
                            </div>
                            <div class="col-md-2">
                                <img src="../../img/<?php echo $logotipo; ?>" alt="Logotipo" style="width: 80px;" id="target-logo">
                            </div>
                            <div class="col-md-4">
                                <label for="icone">Ícone(*.png)</label>
                                <input type="file" class="form-control" id="icone" name="icone" onchange="carregarImgIcone()">
                            </div>
                            <div class="col-md-2">
                                <img src="../../img/<?php echo $icone ?>" alt="Icone" style="width: 80px;" id="target-ico">
                            </div>
                            <input type="hidden" name="id">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="logo_rel">Logotipo Relatório(*.jpg)</label>
                                <input type="file" class="form-control" id="logo-rel" name="logo_rel" onchange="carregarImgLogoRel()">
                            </div>
                            <div class="col-md-2">
                                <img src="../../img/<?php echo $logo_rel; ?>" alt="Logotipo do Relatório" style="width: 80px;"
                                    id="target-logo-rel">
                            </div>
                        </div>
                        <div id="msg-config" class="centro"></div>
                    </div>
                    <div class="modal-footer centro">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Fim Modal Config -->

    <!-- Modal Rel Financeiro -->
    <div class="modal fade" id="modalRelFin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Relatório Financeiro</h4>
                    <button id="btn-fechar-rel" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -25px">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="rel/rel_fin_class.php" target="_blank">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Data Inicial</label>
                                <input type="date" name="dataInicial" class="form-control" value="<?php echo $data_atual ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Data Final</label>
                                <input type="date" name="dataFinal" class="form-control" value="<?php echo $data_atual ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Filtro Data</label>
                                <select name="filtro_data" class="form-control">
                                    <option value="lancamento">Data de Lançamento</option>
                                    <option value="vencimento">Data de Vencimento</option>
                                    <option value="pagamento">Data de Pagamento</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Entradas / Saídas</label>
                                <select name="filtro_tipo" class="form-control">
                                    <option value="receber">Entradas / Ganhos</option>
                                    <option value="pagar">Saídas / Despesas</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Tipo Lançamento</label>
                                <select name="filtro_lancamento" class="form-control">
                                    <option value="">Tudo</option>
                                    <option value="Conta">Ganhos / Despesas</option>
                                    <option value="Serviço">Serviços</option>
                                    <option value="Cancelamento">Devoluções</option>
                                    <option value="Compra">Compras</option>
                                    <option value="Comissão">Comissões</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Pendentes / Pago</label>
                                <select name="filtro_pendentes" class="form-control">
                                    <option value="">Tudo</option>
                                    <option value="Não">Pendentes</option>
                                    <option value="Sim">Pago</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer centro">
                        <button type="submit" class="btn btn-primary">Gerar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Fim Modal Rel Financeiro -->

    <script type="text/javascript">
        function carregarImgPerfil() {
            var target = document.getElementById('target-usu');
            var file = document.querySelector("#foto-perfil").files[0];
            var reader = new FileReader();
            reader.onloadend = function() {
                target.src = reader.result;
            };
            if (file) {
                reader.readAsDataURL(file);
            } else {
                target.src = "";
            }
        }
    </script>

    <script type="text/javascript">
        function carregarImgPaciente() {
            var target = document.getElementById('target-paciente');
            var file = document.querySelector("#foto-paciente").files[0];
            var reader = new FileReader();
            reader.onloadend = function() {
                target.src = reader.result;
            };
            if (file) {
                reader.readAsDataURL(file);
            } else {
                target.src = "";
            }
        }
    </script>

    <script type="text/javascript">
        function carregarImgReceber() {
            var target = document.getElementById('target-arquivo');
            var file = document.querySelector("#arquivo-conta").files[0];
            var reader = new FileReader();
            reader.onloadend = function() {
                target.src = reader.result;
            };
            if (file) {
                reader.readAsDataURL(file);
            } else {
                target.src = "";
            }
        }
    </script>

    <script type="text/javascript">
        $("#form-perfil").submit(function() {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "editar-perfil.php",
                type: 'POST',
                data: formData,
                success: function(mensagem) {
                    $('#msg-perfil').text('');
                    $('#msg-perfil').removeClass()
                    if (mensagem.trim() == "Editado com Sucesso") {
                        $('#btn-fechar-perfil').click();
                        location.reload();
                    } else {
                        $('#msg-perfil').addClass('text-danger')
                        $('#msg-perfil').text(mensagem)
                    }
                },
                cache: false,
                contentType: false,
                processData: false,
            });
        });
    </script>

    <script type="text/javascript">
        $("#form-config").submit(function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "editar-config.php",
                type: 'POST',
                data: formData,
                success: function(mensagem) {
                    $('#msg-config').text('');
                    $('#msg-config').removeClass()
                    if (mensagem.trim() == "Editado com Sucesso") {
                        $('#btn-fechar-config').click();
                        location.reload();
                    } else {
                        $('#msg-config').addClass('text-danger')
                        $('#msg-config').text(mensagem)
                    }
                },
                cache: false,
                contentType: false,
                processData: false,
            });
        });
    </script>

    <script type="text/javascript">
        function carregarImgLogotipo() {
            var target = document.getElementById('target-logo');
            var file = document.querySelector("#logotipo").files[0];
            var reader = new FileReader();
            reader.onloadend = function() {
                target.src = reader.result;
            };
            if (file) {
                reader.readAsDataURL(file);
            } else {
                target.src = "";
            }
        }
    </script>

    <script type="text/javascript">
        function carregarImgLogoRel() {
            var target = document.getElementById('target-logo-rel');
            var file = document.querySelector("#logo-rel").files[0];
            var reader = new FileReader();
            reader.onloadend = function() {
                target.src = reader.result;
            };
            if (file) {
                reader.readAsDataURL(file);
            } else {
                target.src = "";
            }
        }
    </script>

    <script type="text/javascript">
        function carregarImgIcone() {
            var target = document.getElementById('target-ico');
            var file = document.querySelector("#icone").files[0];
            var reader = new FileReader();
            reader.onloadend = function() {
                target.src = reader.result;
            };
            if (file) {
                reader.readAsDataURL(file);
            } else {
                target.src = "";
            }
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // Validação de CPF para qualquer campo com classe .cpf
            document.querySelectorAll(".cpf").forEach(campo => {
                campo.addEventListener("blur", function() {
                    const valor = this.value.trim();
                    if (valor === "") return;

                    if (!validarCPF(valor)) {
                        marcarErro(this, "CPF inválido");
                        this.value = "";
                        this.focus();
                    }
                });
            });

            // Validação de CNPJ para qualquer campo com classe .cnpj
            document.querySelectorAll(".cnpj").forEach(campo => {
                campo.addEventListener("blur", function() {
                    const valor = this.value.trim();
                    if (valor === "") return;

                    if (!validarCNPJ(valor)) {
                        marcarErro(this, "CNPJ inválido");
                        this.value = "";
                        this.focus();
                    }
                });
            });

            // Função de erro visual
            function marcarErro(campo, mensagem) {
                campo.style.border = "2px solid red";

                let msg = document.createElement("div");
                msg.className = "erro-campo";
                msg.style.color = "red";
                msg.style.fontSize = "12px";
                msg.textContent = mensagem;

                campo.parentNode.appendChild(msg);

                setTimeout(() => {
                    campo.style.border = "";
                    msg.remove();
                }, 3000);
            }

        });


        // ✅ Formata como moeda (R$ 0,00 → 0.00 para o banco)
        function formatarMoedaInput(input) {
            let valor = input.value.replace(/\D/g, '');
            valor = (valor / 100).toFixed(2) + '';
            valor = valor.replace('.', ',');
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = valor;
        }

        // ✅ Limpa para enviar ao banco (R$ 0,00 → 0.00)
        function limparMoeda(valor) {
            return valor.replace('R$', '').replace(/\./g, '').replace(',', '.').trim();
        }

        // ✅ Aplica formatação nos campos de multa/juros
        document.addEventListener('DOMContentLoaded', function() {
            const camposMoeda = document.querySelectorAll('#multa_padrao, #juros_padrao');

            camposMoeda.forEach(input => {
                // Formata ao digitar
                input.addEventListener('input', function() {
                    let valor = this.value.replace(/\D/g, '');
                    if (valor !== '') {
                        valor = (valor / 100).toFixed(2) + '';
                        valor = valor.replace('.', ',');
                        valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
                        this.value = valor;
                    }
                });
                // Formata ao sair do campo
                input.addEventListener('blur', function() {
                    if (this.value === '' || this.value === '0') {
                        this.value = '0,00';
                    }
                });
            });

            // ✅ Limpa valores antes de enviar o formulário
            const formConfig = document.getElementById('form-config');
            if (formConfig) {
                formConfig.addEventListener('submit', function(e) {
                    const multa = document.getElementById('multa_padrao');
                    const juros = document.getElementById('juros_padrao');

                    if (multa) multa.value = limparMoeda(multa.value);
                    if (juros) juros.value = limparMoeda(juros.value);
                });
            }
        });
    </script>