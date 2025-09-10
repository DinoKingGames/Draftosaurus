<?php

echo "Conectando base de datos";

$db = mysqli_connect(localhost, 'root', 'root', 'dinoking_database');

if (!$db) {
    die('Error de conexión: ' . mysqli_connect_error());
}

echo "Conexión exitosa!";

?>|