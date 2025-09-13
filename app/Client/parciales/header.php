<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<?php
  $user = $_SESSION['user'] ?? null;
  $isAdmin = isset($user['rol']) && in_array($user['rol'], ['admin', 'superadmin'], true);
?>
<header class="site-header">
    <div class="container">
        <div class="barra">
            <a href="?page=inicio" class="logo-contenedor">
                <img src="<?= asset('imgs/logo_final.png') ?>" alt="Logo Final">
                <h1 class="m0">Dino<span>King Games</span></h1>
            </a>
            <div class="navegacion">
                <a href="?page=jugar">Jugar</a>
                <a href="?page=seguimiento">Seguimiento</a>
                <a href="?page=nosotros">Nosotros</a>
                <a href="?page=contacto">Contacto</a>

                <?php if ($user): ?>
                    <span class="user-badge" title="Sesión iniciada">
                      <span class="status-dot" aria-hidden="true"></span>
                      <span class="user-name"><?= htmlspecialchars($user['nombre']) ?></span>
                    </span>

                    <?php if ($isAdmin): ?>
                      <a href="?page=admin" class="btn-admin" title="Panel de administración">Panel</a>
                    <?php endif; ?>

                    <a href="#" id="logoutLink" class="btn-auth btn-logout" title="Cerrar sesión">Salir</a>
                <?php else: ?>
                    <a href="#" id="openModal" class="btn-auth">Iniciar sesión</a>
                    <a href="#" id="openRegister" class="btn-auth">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="texto-header">
            <h2 class="m0">¡Descubre nuestros juegos!</h2>
            <p class="m0">Diviertete con amigos mientras disfrutas un desafio.</p>
        </div>
    </div>
</header>