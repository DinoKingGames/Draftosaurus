<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego</title>
    <link rel="icon" href="imgs/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|PT+Sans:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="../Client/main.js"></script>
</head>

<body>

<?php
    require __DIR__ . '/../Controllers/DinosaurioController.php';

    $controller = new DinosaurioController();
    $bandeja = $controller->asignacion();
?>

<div class="contenedor-juego">
    <div class="tablero" id="tablero">
        <img src="Client/imgs/juego.jpg" alt="Tablero de juego" class="imagen-tablero">
    </div>

    <div class="bandeja">
        <h3>Dinosaurios</h3>
        <div class="dinosaurios">
            <?php
                $vistos = []; 
                foreach ($bandeja as $dino) {
                    if (!in_array($dino->tipo, $vistos)) {
                        echo '<img src="' . $dino->imagen . '" 
                                    alt="' . $dino->tipo . '"
                                    data-tipo="' . $dino->tipo . '" 
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
