<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|PT+Sans:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/normalize.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>
<body>
    <?php view_partial('header_simple'); ?>
    <main class="container">
        <h2 class="m0 text-center">Contacto</h2>
        <div class="grid centrar-contacto">
            <div class="columnas-12">
                <img src="<?= asset('imgs/dino_telefono.png') ?>" alt="Imagen Contacto" class="imagen-contacto">
            </div>
            <div class="formulario-contacto columnas-10">
                <form action="#">
                    <div class="campo">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" placeholder="Tu Nombre">
                    </div>

                    <div class="campo">
                        <label for="email">Email</label>
                        <input type="text" id="email" placeholder="Tu Correo Electronico">
                    </div>

                    <div class="campo">
                        <label for="mensaje">Mensaje</label>
                        <textarea id="mensaje" placeholder="Ingresa tu Mensaje"></textarea>
                    </div>

                    <div class="campo enviar">
                        <input type="submit" value="Enviar" class="btn btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php view_partial('footer'); ?>
</body>
</html>