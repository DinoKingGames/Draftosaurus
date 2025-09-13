<footer class="site-footer">
    <div class="barra container">
        <a href="?page=inicio" class="logo-contenedor">
                <img src="<?= asset('imgs/logo_final.png') ?>" alt="Logo Final">
            <p class="m0">Dino<span>King Games</span></p>
        </a>
        <div class="navegacion">
            <a href="?page=jugar">Jugar</a>
            <a href="?page=seguimiento">Seguimiento</a>
            <a href="?page=nosotros">Nosotros</a>
            <a href="?page=contacto">Contacto</a>
        </div>
    </div>
    <?php view_partial('auth-modals'); ?>
    <script src="<?= asset('js/auth.js') ?>" defer></script>

</footer>