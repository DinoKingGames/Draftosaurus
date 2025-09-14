<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <link rel="icon" href="<?= asset(path: 'imgs/favicon.ico') ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|PT+Sans:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/normalize.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>
<body>
    <?php view_partial('header'); ?>
    <div class="contenido-principal container">
        <main class="blog">
            <h2>Conoce Draftosaurus</h2>
            <article class="entrada-blog">
                <div class="imagen">
                    <img src="<?= asset('imgs/draftosaurus_intro.jpg') ?>" alt="">
                </div>
                <div class="entrada-blog">
                    <h3 class="m0">¿Qué es DinoKing?</h3>
                    <p>En DinoKing Games nos apasiona llevar los juegos de mesa al mundo digital. Creamos versiones pequeñas y accesibles de juegos reales, manteniendo la diversión original pero adaptada para que puedas disfrutarlos desde tu computadora o dispositivo, cuando quieras y donde quieras.</p>
                    <a href="?page=nosotros" class="btn btn-primary">Sobre Nosotros</a>
                </div>
            </article>
            <article class="entrada-blog">
                <div class="imagen">
                    <img src="<?= asset('imgs/dino_confundido.jpg') ?>" alt="">
                </div>
                <div class="entrada-blog">
                    <h3 class="no-margin">¡Aplicación de seguimiento!</h3>
                    <p>En DinoKing Games sabemos lo tedioso que es hacer el conteo de puntos en juegos de mesa. Por eso desarrollamos una aplicación de seguimiento que te permita concentrarte en tu partida. ¡Nosotros calculamos los puntos por ti!</p>
                    <a href="?page=seguimiento" class="btn btn-primary">Ve a la aplicación</a>
                </div>
            </article>
            <article class="entrada-blog">
                <div class="imagen">
                    <img src="<?= asset('imgs/drafto_web.png') ?>" alt="">
                </div>
                <div class="entrada-blog">
                    <h3 class="m0">Jugar Online</h3>
                    <p>Experiencia por cuenta propia el maravilloso parque de Draftosaurus en su versión de verano ya mismo de forma gratuita. ¡Invita a tus amigos y juega ya!</p>
                    <a href="?page=jugar" class="btn btn-primary">Juega Draftosaurus</a>
                </div>
            </article>
        </main>

        <aside class="cursos">
            <h2 class="m0">Nuestros proximos juegos</h2>
            <ul class="cursos-lista">
                <li class="futuros">
                    <h3>Dinosaur Island: Rawr ‘n Write</h3>
                    <p class="m0"><span>Fecha:</span> 23/2/2026</p>
                    <p class="m0"><span>Precio:</span> Gratis </p>
                    <a href="?page=proximamente" class="btn btn-secondary">Mas información</a>
                </li>
                <li class="futuros">
                    <h3 class="m0">Dinosaur World</h3>
                    <p class="m0"><span>Precio:</span> 12/4/2026</p>
                    <p class="m0"><span>Cupo:</span> Gratis </p>
                    <a href="?page=proximamente" class="btn btn-secondary">Mas información</a>
                </li>
            </ul>
        </aside>
    </div>
    <?php view_partial('footer'); ?>
</body>
</html>