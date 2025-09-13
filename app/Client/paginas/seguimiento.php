<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento</title>

    <link rel="icon" href="<?= asset('imgs/favicon.ico') ?>" type="image/x-icon">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans|PT+Sans:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/normalize.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">

    <script src="<?= asset('js/main.js') ?>" defer></script>
</head>
<body>
    <?php view_partial('header_simple'); ?>
    <h2 class="center">Tablero Dinosaurios</h2>

    <div class="tablero-seguimiento">
        <!-- FILA 1 -->
        <div class="celda bosque"><button id="btn1" onclick="selectUno()" class="btn-tamaño bosque-color btn-opcion"></button></div>
        <div class="celda rio"><button id="btn2" onclick="selectDos()" class="btn-tamaño rio-color btn-opcion"></button></div>
        <div class="celda bosque"><button id="btn3" onclick="selectTres()" class="btn-tamaño bosque-color btn-opcion"></button></div>

        <!-- FILA 2 -->
        <div class="celda bosque"><button id="btn4" onclick="selectCuatro()" class="btn-tamaño bosque-color btn-opcion"></button></div>
        <div class="celda roca"><button id="btn5" onclick="selectCinco()" class="btn-tamaño roca-color btn-opcion"></button></div>

        <!-- FILA 3 -->
        <div class="celda roca"><button id="btn6" onclick="selectSeis()" class="btn-tamaño roca-color btn-opcion"></button></div>
        <div class="celda roca"><button id="btn7" onclick="selectSiete()" class="btn-tamaño roca-color btn-opcion"></button></div>

        <!-- FILA 4 - DEPÓSITO -->
        <div class="celda deposito">
            <div class="dinosaurios-iconos">
                <button onclick="slctRojo()" class="no-border"><img src="<?= asset('imgs/minis/rojo.png') ?>" alt="Rojo" class="icono" id="ico-rojo"></button>
                <button onclick="slctCyan()" class="no-border"><img src="<?= asset('imgs/minis/cyan.png') ?>" alt="Cyan" class="icono" id="ico-cyan"></button>
                <button onclick="slctNaranja()" class="no-border"><img src="<?= asset('imgs/minis/naranja.png') ?>" alt="Naranja" class="icono" id="ico-naranja"></button>
                <button onclick="slctRosa()" class="no-border"><img src="<?= asset('imgs/minis/rosa.png') ?>" alt="Rosa" class="icono" id="ico-rosa"></button>
                <button onclick="slctVerde()" class="no-border"><img src="<?= asset('imgs/minis/verde.png') ?>" alt="Verde" class="icono" id="ico-verde"></button>
                <button onclick="slctAzul()" class="no-border"><img src="<?= asset('imgs/minis/azul.png') ?>" alt="Azul" class="icono" id="ico-azul"></button>
            </div>
        </div>
    </div>

    <div id="mensaje" class="mensaje"></div>
    <?php view_partial('footer'); ?>
</body>
</html>