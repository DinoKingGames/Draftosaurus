<?php
// API inline: permite usar ?action=init|get_hand|place y también ?init=1 como alias de init.
if (isset($_GET['init']) && !isset($_REQUEST['action'])) {
    $_GET['action'] = 'init';
    $_REQUEST['action'] = 'init';
}
if (isset($_REQUEST['action'])) {
    require_once __DIR__ . '/../Controllers/JuegoControlador.php';
    JuegoControlador::handleRequest();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego</title>
    <link rel="icon" href="/Client/imgs/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|PT+Sans:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Client/css/normalize.css">
    <link rel="stylesheet" href="/Client/css/styles.css">
    <script src="/Client/main.js" defer></script>


</head>

<body>

<?php
    require __DIR__ . '/../Controllers/DinosaurioController.php';
    $controller = new DinosaurioController();
    $bandeja = $controller->asignacion();
?>

<section id="pantalla-inicio" class="pantalla-inicio">
  <div class="inicio-card">
    <img
      src="/Client/imgs/dinoIntroFinal.gif"
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

<div class="contenedor-juego hidden">
    <div class="tablero" id="tablero">
        <img src="/Client/imgs/juego.jpg" alt="Tablero de juego" class="imagen-tablero">
    </div>

    <div class="bandeja">
        <h3>Dinosaurios</h3>
        <div class="dinosaurios">
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

</body>
</html>