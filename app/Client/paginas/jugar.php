<?php
if (isset($_GET['init']) && !isset($_REQUEST['action'])) {
    $_GET['action'] = 'init';
    $_REQUEST['action'] = 'init';
}
if (isset($_REQUEST['action'])) {
    $ctrlFile1 = APP_PATH . '/Controllers/JuegoControlador.php';
    $ctrlFile2 = APP_PATH . '/Controllers/JuegoController.php';
    if (file_exists($ctrlFile1)) {
        require_once $ctrlFile1;
    } elseif (file_exists($ctrlFile2)) {
        require_once $ctrlFile2;
    }

    if (class_exists('JuegoControlador')) {
        JuegoControlador::handleRequest();
    } elseif (class_exists('JuegoController')) {
        JuegoController::handleRequest();
    } else {
        http_response_code(500);
        echo 'Controller no encontrado';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego</title>

    <link rel="icon" href="<?= asset('imgs/favicon.ico') ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|PT+Sans:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/normalize.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
    <script src="<?= asset('js/main.js') ?>" defer></script>
</head>
<body>

<?php view_partial('header_simple'); ?>

<?php
    $dinoCtrlFile = APP_PATH . '/Controllers/DinosaurioController.php';
    if (file_exists($dinoCtrlFile)) {
        require_once $dinoCtrlFile;
        $controller = new DinosaurioController();
        $bandeja = $controller->asignacion();
    } else {
        $bandeja = [];
    }
?>

<section id="pantalla-inicio" class="pantalla-inicio">
  <div class="inicio-card">
    <img
      src="<?= asset('imgs/dinoIntroFinal.gif') ?>"
      alt="Dinosaurio de bienvenida"
      class="intro-dino"
      width="220"
      height="220"
      loading="eager"
    >
    <h2 class="m0 text-center">Draftosaurus</h2>
    <p class="text-center">¡Que empiece la aventura jurasica!</p>
    <button id="btn-iniciar" class="btn btn-primary">Jugar</button>
    <p id="init-error" class="init-error hidden">No se pudo iniciar la partida. Intenta nuevamente.</p>
  </div>
</section>

<div class="juego-multi">
  <div class="contenedor-juego hidden" data-player="1">
    <h3 style="margin: 8px 0;">Jugador 1</h3>
    <div class="tablero" id="tablero-1">
      <img src="<?= asset('imgs/juego.jpg') ?>" alt="Tablero de juego" class="imagen-tablero">
    </div>
    <div class="bandeja">
      <h4>Dinosaurios</h4>
      <div class="dinosaurios" id="bandeja-1">
        <?php
          $vistos = [];
          foreach ($bandeja as $dino) {
              if (!in_array($dino->tipo, $vistos)) {
                  echo '<img src="' . htmlspecialchars($dino->imagen, ENT_QUOTES, 'UTF-8') . '"
                              alt="' . htmlspecialchars($dino->tipo, ENT_QUOTES, 'UTF-8') . '"
                              data-tipo="' . htmlspecialchars($dino->tipo, ENT_QUOTES, 'UTF-8') . '"
                              class="mini-dino"
                              draggable="true">';
                  $vistos[] = $dino->tipo;
              }
          }
        ?>
      </div>
    </div>
  </div>

  <div class="contenedor-juego hidden" data-player="2">
    <h3 style="margin: 8px 0;">Jugador 2</h3>
    <div class="tablero" id="tablero-2">
      <img src="<?= asset('imgs/juego.jpg') ?>" alt="Tablero de juego" class="imagen-tablero">
    </div>
    <div class="bandeja">
      <h4>Dinosaurios</h4>
      <div class="dinosaurios" id="bandeja-2">
        <?php
          $vistos = [];
          foreach ($bandeja as $dino) {
              if (!in_array($dino->tipo, $vistos)) {
                  echo '<img src="' . htmlspecialchars($dino->imagen, ENT_QUOTES, 'UTF-8') . '"
                              alt="' . htmlspecialchars($dino->tipo, ENT_QUOTES, 'UTF-8') . '"
                              data-tipo="' . htmlspecialchars($dino->tipo, ENT_QUOTES, 'UTF-8') . '"
                              class="mini-dino"
                              draggable="true">';
                  $vistos[] = $dino->tipo;
              }
          }
        ?>
      </div>
    </div>
  </div>
</div>

<?php view_partial('footer'); ?>

</body>
</html>