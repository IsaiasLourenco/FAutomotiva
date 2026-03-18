<footer class="rodape">
    <?php echo $nome_sistema ?><br>
    <?php echo $endereco_sistema ?>
    <a href="https://api.whatsapp.com/send?phone=<?php echo $telefone_url ?>; ?>
            &text=Ol%C3%A1!%20Gostaria%20de%20marcar%20uma%20consultao%20no%20seu%20Consultório."
        target="_blank"
        class="link-neutro"><br>
        <i class="fab fa-whatsapp text-success"></i>&nbsp;<?php echo $telefone_sistema ?>
    </a>
    <a href="<?php echo $instagram_sistema; ?>" style="color: #E1306C; text-decoration: none;" target="_blank">
        <i class="fab fa-instagram"></i>&nbsp;Instagram
    </a><br>
    <span>
        Desenvolvido por:
        <a href="<?php echo $site_dev; ?>" style="font-weight: bold; color: #306ee1; text-decoration: none;" target="_blank">
        &nbsp;&nbsp;<i class="fa fa-desktop"></i>&nbsp;<?php echo $desenvolvedor?>
    </a>
    </span>
</footer>