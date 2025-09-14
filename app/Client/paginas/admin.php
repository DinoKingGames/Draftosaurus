<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
$userId = 1;
if (isset($_SESSION['usuario']['id']))
    $userId = (int) $_SESSION['usuario']['id'];
elseif (isset($_SESSION['usuario_id']))
    $userId = (int) $_SESSION['usuario_id'];
elseif (isset($_SESSION['user']['id']))
    $userId = (int) $_SESSION['user']['id'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Admin · Sistema de Roles</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="<?= asset('imgs/favicon.ico') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('css/normalize.css') ?>" />
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>" />
    <script>
        window.ADMIN_USER_ID = <?= (int) $userId ?>;
    </script>
    <script src="<?= asset('js/admin.js') ?>" defer></script>
</head>

<body>
    <?php view_partial('header_simple'); ?>

    <main class="admin-roles-wrap">
        <div class="admin-card">
            <div class="header">
                <h1>Panel de Administración</h1>
            </div>
            <div class="content">
                <div id="admin-user-list" class="admin_user_list"></div>

                <div class="role-selection">
                    <h3 class="text-center">Selecciona un nuevo rol:</h3>
                    <div class="roles">
                        <div class="role-option" data-role="superadmin"><i>🛡️</i>
                            <div>Crear</div>
                        </div>
                        <div class="role-option" data-role="admin"><i>👑</i>
                            <div>Eliminar</div>
                        </div>
                        <div class="role-option " data-role="user"><i>👤</i>
                            <div>Editar</div>
                        </div>
                        <div class="role-option " data-role="user"><i>👤</i>
                            <div>Rol</div>
                        </div>
                    </div>
                </div>

                <button class="btn" id="change-role-btn">
                    <span id="btn-text">Cambiar Rol</span>
                    <span id="btn-loading" class="loading" style="display:none;"></span>
                </button>

                <div class="message" id="message"></div>

                <div class="history">
                    <h3 class="text-center">Historial de cambios</h3>
                    <div id="history-container"></div>
                </div>
            </div>
        </div>
    </main>

    <?php view_partial('footer'); ?>
</body>

</html>