<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<?php
  $user = $_SESSION['user'] ?? null;
  $isAdmin = isset($user['rol']) && in_array($user['rol'], ['admin', 'superadmin'], true);
?>
<header class="header-simple">
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
                      <svg class="user-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path>
                        <path d="M4.5 20.25a8.25 8.25 0 1 1 15 0"></path>
                      </svg>
                      <span class="user-name"><?= htmlspecialchars($user['nombre']) ?></span>
                      <span class="status-dot" aria-hidden="true"></span>
                    </span>

                    <?php if ($isAdmin): ?>
                      <a href="?page=admin" class="btn-admin" title="Panel de administración">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                          <path d="M9.594 3.94c.09-.542.872-.542.963 0l.149.9c.07.424.47.72.9.674l.91-.1c.551-.06.866.63.47 1.01l-.665.64a.75.75 0 0 0-.22.71l.21.9c.127.537-.45.958-.922.67l-.79-.48a.75.75 0 0 0-.78 0l-.79.48c-.472.288-1.05-.133-.922-.67l.21-.9a.75.75 0 0 0-.22-.71l-.665-.64c-.396-.38-.081-1.07.47-1.01l.91.1a.75.75 0 0 0 .9-.674l.149-.9Z"></path>
                          <path d="M12 14.25a4.5 4.5 0 0 0-4.5 4.5v.75h9v-.75a4.5 4.5 0 0 0-4.5-4.5Z"></path>
                        </svg>
                        <span>Panel</span>
                      </a>
                    <?php endif; ?>

                    <a href="#" id="logoutLink" class="btn-auth btn-logout" title="Cerrar sesión">
                      <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15"></path>
                        <path d="M12 9l3-3m0 0 3 3m-3-3v12"></path>
                      </svg>
                      <span>Salir</span>
                    </a>
                <?php else: ?>
                    <a href="#" id="openModal" class="btn-auth">
                      <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15"></path>
                        <path d="M12 15l3 3m0 0 3-3m-3 3V6"></path>
                      </svg>
                      <span>Iniciar sesión</span>
                    </a>
                    <a href="#" id="openRegister" class="btn-auth">
                      <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path>
                        <path d="M4.5 20.25a8.25 8.25 0 1 1 15 0"></path>
                        <path d="M19.5 8.25v3"></path>
                        <path d="M21 9.75h-3"></path>
                      </svg>
                      <span>Registrarse</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>