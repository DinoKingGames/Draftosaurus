# Draftosaurus
Proyecto de Draftosaurus por el equipo de DinoKing Games. Trabajo del proyecto final de 3° de informática (ITI).

La documentación de Draftosaurus para sus respectivas materias se encuentra dentro de la carpeta "Documentación/(Nombre de materia)".

Este proyecto se realiza con HTML, CSS y JavaScript en el cliente, y PHP + MySQL en el backend (servidor web Apache/Nginx o el servidor embebido de PHP). La app guarda el estado de la partida en una base de datos y utiliza sesiones para la autenticación.

Puedes ver y utilizar el estado previo del proyecto, con el Front End y aplicación de seguimiento en: https://dinoking.netlify.app/


## Requisitos

- PHP 8.0 o superior (recomendado 8.2+)
  - Extensión mysqli habilitada
- MySQL 5.7+ o MySQL 8.0+ (recomendado)
- Servidor web (Apache/Nginx) o, para desarrollo local, el servidor embebido de PHP
- Opcional: XAMPP

## Instalación rápida (local)
1) En DinoKing trabajamos con git y git bash para realizar toda la comunicación con el repositorio.

2) No es una necesidad, pero recomendamos trabajar de la misma forma con GIT y clonar el proyecto desde la terminal.

3) Clonar el repositorio
```bash
git clone https://github.com/DinoKingGames/Draftosaurus.git
cd Draftosaurus
```

4) Crear la base de datos y tablas
Ejecuta primero el schema y luego los triggers:
```bash
# Crea BD, tablas y seed del usuario inicial
mysql -u root -p < app/Database/schema.sql

# Crea triggers (ej. único superadmin)
mysql -u root -p < app/Database/triggers_superadmin.sql
```

5) Configurar las variables de entorno de la conexión
Por defecto presentamos la siguiente configuración utilizable en este proyecto.

```
DB_HOST='127.0.0.1'(O localhost)
DB_PORT='3306'
DB_NAME='dinoking_database'
DB_USER='root'
DB_PASS='root'
```


4) Levantar el servidor en desarrollo
```bash
php -S localhost:8000 -t public
```
Luego abre http://localhost:8000


## Documentación
Dentro de la estructura del proyecto, se puede encontrar una carpeta "Documentación" conteniendo los informes de las materias FullStack e Ingenieria de Software, con recursos necesarios para las mismas.


## Habilitar mysqli (si te da error de conexión)

- Linux (Debian/Ubuntu):
  ```bash
  sudo apt-get install php-mysql
  sudo service apache2 restart  # o php-fpm/nginx según tu stack
  ```

- Windows (XAMPP/WAMP/PHP):
  - Edita `php.ini` y descomenta la línea:
    ```
    extension=mysqli
    ```
  - Reinicia Apache (En caso de estar usando WAMP o XAMPP).


## Usuario inicial y cambio de contraseña

El `schema.sql` inserta un usuario inicial con rol `superadmin`. La contraseña está hasheada (bcrypt). Para definir tu propia contraseña:

1) Genera un hash en PHP:
Inserta un usuario nuevo desde el menu de registro de la aplicación o desde la consola SQL. En el schema se encuentran 4 usuarios administradores correspondientes a los miembros del grupo y un usuario "admin" general, con rol de superadmin (Inmovible).
```SQL  
INSERT INTO usuarios (nombre, email, contrasena, rol) VALUES ('nombre', 'correo', 'contraseña_hasheada', 'rol');

```

1.2) Contrasñea Hasheada
Es importante no ingresar la contraseña sin hashear en la base de datos, en caso de ser necesario recomendamos ejecutar el siguiente comando en la consola y pegar el resultado en el value de "contraseña_hasheada" al insertar el registro.
```bash  
php -r "echo password_hash('demostracion', PASSWORD_DEFAULT);"
```

Recomendación: crea tu propio usuario admin/superadmin y deshabilita/elimina el usuario inicial en entornos reales.




