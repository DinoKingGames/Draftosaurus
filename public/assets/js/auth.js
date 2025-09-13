document.addEventListener('DOMContentLoaded', () => {
  // --------- Apertura/cierre de modales ---------
  const openLoginBtn = document.getElementById('openModal');
  const openRegisterBtn = document.getElementById('openRegister');
  const loginModal = document.getElementById('loginModal');
  const registerModal = document.getElementById('registerModal');
  const closeLoginBtn = loginModal ? loginModal.querySelector('.close-modal') : null;
  const closeRegisterBtn = registerModal ? registerModal.querySelector('.close-register') : null;
  const switchToLogin = document.querySelector('#registerModal .forgotten');

  const lockScroll = () => document.body.classList.add('modal-open');
  const unlockScroll = () => document.body.classList.remove('modal-open');
  const openModalEl = (modal) => {
    if (!modal) return;
    modal.style.display = 'flex';
    lockScroll();
    const firstInput = modal.querySelector('input, select, textarea, button');
    if (firstInput) firstInput.focus();
  };
  const closeModalEl = (modal) => {
    if (!modal) return;
    modal.style.display = 'none';
    unlockScroll();
  };

  if (openLoginBtn && loginModal) {
    openLoginBtn.addEventListener('click', (e) => { e.preventDefault(); openModalEl(loginModal); });
  }
  if (closeLoginBtn && loginModal) {
    closeLoginBtn.addEventListener('click', () => closeModalEl(loginModal));
  }
  if (loginModal) {
    window.addEventListener('click', (e) => { if (e.target === loginModal) closeModalEl(loginModal); });
  }

  if (openRegisterBtn && registerModal) {
    openRegisterBtn.addEventListener('click', (e) => { e.preventDefault(); openModalEl(registerModal); });
  }
  if (closeRegisterBtn && registerModal) {
    closeRegisterBtn.addEventListener('click', () => closeModalEl(registerModal));
  }
  if (registerModal) {
    window.addEventListener('click', (e) => { if (e.target === registerModal) closeModalEl(registerModal); });
  }

  if (switchToLogin && registerModal && loginModal) {
    switchToLogin.addEventListener('click', (e) => {
      e.preventDefault();
      closeModalEl(registerModal);
      openModalEl(loginModal);
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (loginModal && loginModal.style.display === 'flex') closeModalEl(loginModal);
      if (registerModal && registerModal.style.display === 'flex') closeModalEl(registerModal);
    }
  });

  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');

  const showErrors = (form, errors) => {
    const box = form.querySelector('.form-errors');
    if (!box) return;
    if (!errors || errors.length === 0) {
      box.style.display = 'none';
      box.innerHTML = '';
      return;
    }
    box.innerHTML = `<ul style="margin:0;padding-left:1rem;">${errors.map(e => `<li>${escapeHtml(e)}</li>`).join('')}</ul>`;
    box.style.display = 'block';
  };

  const escapeHtml = (str) => String(str)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const API_BASE = 'api.php';

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      showErrors(loginForm, []);
      const email = loginForm.email.value.trim();
      const password = loginForm.password.value;

      try {
        const res = await fetch(`${API_BASE}/auth/login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ email, password })
        });
        const data = await res.json();

        if (!res.ok || data.ok === false) {
          const errs = data?.errors || ['No se pudo iniciar sesión.'];
          showErrors(loginForm, errs);
          return;
        }

        closeModalEl(loginModal);
        window.location.reload();
      } catch (err) {
        showErrors(loginForm, ['Error de red. Inténtalo de nuevo.']);
      }
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      showErrors(registerForm, []);
      const nombre = registerForm.nombre.value.trim();
      const email = registerForm.email.value.trim();
      const password = registerForm.password.value;
      const password_confirm = registerForm.password_confirm.value;

      try {
        const res = await fetch(`${API_BASE}/auth/register`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ nombre, email, password, password_confirm })
        });
        const data = await res.json();

        if (!res.ok || data.ok === false) {
          const errs = data?.errors || ['No se pudo registrar.'];
          showErrors(registerForm, errs);
          return;
        }

        closeModalEl(registerModal);
        window.location.reload();
      } catch (err) {
        showErrors(registerForm, ['Error de red. Inténtalo de nuevo.']);
      }
    });
  }
});