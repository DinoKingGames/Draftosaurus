<?php
// Delegar acciones (JSON limpio) al controlador
if (isset($_REQUEST['action'])) {
    require_once APP_PATH . '/Controllers/JuegoControlador.php';
    JuegoControlador::handleRequest();
    exit;
}

// Detectar usuario logueado (ajusta a tu sistema de auth)
if (session_status() === PHP_SESSION_NONE) session_start();
$userId = 0;
if (isset($_SESSION['usuario']['id'])) $userId = (int)$_SESSION['usuario']['id'];
elseif (isset($_SESSION['usuario_id'])) $userId = (int)$_SESSION['usuario_id'];
elseif (isset($_SESSION['user']['id'])) $userId = (int)$_SESSION['user']['id'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Draftosaurus</title>
  <link rel="stylesheet" href="<?= asset('css/normalize.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
  <script>
    // Exponer user id al front
    window.GAME_USER_ID = <?= (int)$userId ?>;
  </script>
  <script src="<?= asset('js/main.js') ?>" defer></script>
</head>
<body>
<?php view_partial('header_simple'); ?>

<section id="pantalla-inicio" class="pantalla-inicio">
  <div class="inicio-card">
    <img src="<?= asset('imgs/dinoIntroFinal.gif') ?>" alt="" class="intro-dino" width="220" height="220">
    <h2 class="m0 text-center">Draftosaurus</h2>
    <p class="text-center">¡Que empiece la aventura jurásica!</p>
    <button id="btn-iniciar" class="btn btn-primary">Jugar</button>
    <p id="init-error" class="init-error hidden">No se pudo iniciar la partida. Intenta nuevamente.</p>
  </div>
</section>

<div class="container" style="margin-top:16px;">
  <p id="mensaje" class="text-center"></p>
</div>

<div class="juego-multi">
  <div class="contenedor-juego hidden" data-player="1">
    <h3>Jugador 1 · Puntos: <span id="score-1">0</span></h3>
    <div class="tablero" id="tablero-1">
      <img src="<?= asset('imgs/juego.jpg') ?>" alt="Tablero" class="imagen-tablero">
    </div>
    <div class="bandeja">
      <h4>Dinosaurios</h4>
      <div class="dinosaurios" id="bandeja-1"></div>
    </div>
  </div>

  <div class="contenedor-juego hidden" data-player="2">
    <h3>Jugador 2 · Puntos: <span id="score-2">0</span></h3>
    <div class="tablero" id="tablero-2">
      <img src="<?= asset('imgs/juego.jpg') ?>" alt="Tablero" class="imagen-tablero">
    </div>
    <div class="bandeja">
      <h4>Dinosaurios</h4>
      <div class="dinosaurios" id="bandeja-2"></div>
    </div>
  </div>
</div>

<?php view_partial('footer'); ?>
</body>
</html>