<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="icon" href="<?= asset('imgs/favicon.ico') ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|PT+Sans:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/normalize.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>
<body>
    <?php view_partial('header_simple'); ?>
    <main class="container">
        <h2 class="text-center">Nuestros proximos juegos</h2>

        <article class="entrada-blog grid">
            <div class="columnas-4">
                <img src="<?= asset('imgs/rwr_write.png') ?>" alt="">
            </div>
            <div class="columnas-8">
                <h3 class="m0">Dinosaur Island: Rawr ‘n Write</h3>
                <p class="m0"><span>Precio:</span> Gratis</p>
                <p class="m0"><span>Fecha:</span> 20 de Febrero de 2026</p>
                <br>
                <p class="m0">Dinosaur Island: Rawr ‘n Write es un juego donde usás dados y dibujás tu propio parque de dinosaurios. ¡Elegís dinos, contratás científicos y hasta planificás tus atracciones! Es rápido, divertido y perfecto para chicos que aman los lápices, los planes… ¡y los dinosaurios! </p>
            </div>
        </article>

        <article class="entrada-blog grid">
            <div class="columnas-4">
                <img src="<?= asset('imgs/dino_world.png') ?>" alt="">
            </div>
            <div class="columnas-8 ">
                <h3 class="m0">Dinosaur World</h3>
                <p class="m0"><span>Precio:</span> Gratis</p>
                <p class="m0"><span>Fecha:</span> 14 de Abril de 2026</p>
                <br>
                <p class="m0">¡Bienvenidos a Dinosaur World, donde podés construir el parque jurásico de tus sueños! En este juego, usás fichas y losetas para diseñar caminos, criar dinosaurios y hacer felices a los visitantes… ¡si es que no se los come un T-Rex! Ideal para chicos más grandes o para jugar en familia con mucha emoción y aventura.</p>
            </div>
        </article>
    </main>
    <?php view_partial('footer'); ?>
</body>
</html>