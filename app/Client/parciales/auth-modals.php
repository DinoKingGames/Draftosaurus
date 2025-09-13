<!-- LOGIN MODAL -->
<div id="loginModal" class="modal-overlay" aria-hidden="true" role="dialog">
  <div class="modal-content" role="document">
    <button type="button" class="close-modal" aria-label="Cerrar">&times;</button>
    <section class="form-log">
      <div class="form-wrap">
        <!-- action sirve como fallback si no hay JS -->
        <form id="loginForm" action="?page=login&action=login" method="post" class="login" novalidate>
          <legend>Bienvenido</legend>

          <div class="form-errors" role="alert" aria-live="polite" style="display:none;"></div>

          <label for="login_email">Email</label>
          <input type="email" id="login_email" name="email" placeholder="tu@email.com" required>

          <label for="login_password">Contraseña</label>
          <input type="password" id="login_password" name="password" placeholder="Contraseña" required>

          <input type="submit" value="Ingresar" class="boton-login">
          <a href="#" class="forgotten">¿Olvidaste tu contraseña?</a>
        </form>
      </div>
    </section>
  </div>
</div>

<!-- REGISTER MODAL -->
<div id="registerModal" class="modal-overlay" aria-hidden="true" role="dialog">
  <div class="modal-content" role="document">
    <button type="button" class="close-modal close-register" aria-label="Cerrar">&times;</button>
    <section class="form-log">
      <div class="form-wrap">
        <form id="registerForm" action="?page=registro&action=register" method="post" class="login" novalidate>
          <legend>Registrarse</legend>

          <div class="form-errors" role="alert" aria-live="polite" style="display:none;"></div>

          <label for="reg_nombre">Nombre de usuario</label>
          <input type="text" id="reg_nombre" name="nombre" placeholder="Tu nombre" required>

          <label for="reg_email">Correo electrónico</label>
          <input type="email" id="reg_email" name="email" placeholder="ejemplo@gmail.com" required>

          <label for="reg_password">Contraseña</label>
          <input type="password" id="reg_password" name="password" placeholder="Contraseña" required>

          <label for="reg_password_confirm">Confirmar contraseña</label>
          <input type="password" id="reg_password_confirm" name="password_confirm" placeholder="Confirmar contraseña" required>

          <input type="submit" value="Registrarse" class="boton-login">
          <a href="#" class="forgotten">¿Ya tienes cuenta? Inicia sesión</a>
        </form>
      </div>
    </section>
  </div>
</div>