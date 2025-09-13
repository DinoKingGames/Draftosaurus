document.addEventListener('DOMContentLoaded', () => {
  const openModal = document.getElementById('openModal');
  const loginModal = document.getElementById('loginModal');
  const closeModalBtn = loginModal ? loginModal.querySelector('.close-modal') : null;

  const registerModal = document.getElementById('registerModal');
  const closeRegister = registerModal ? registerModal.querySelector('.close-register') : null;
  const switchToLogin = document.querySelector('#registerModal .forgotten');

  // LOGIN 
  if (openModal && loginModal && closeModalBtn) {
    openModal.addEventListener('click', (e) => {
      e.preventDefault();
      loginModal.style.display = 'flex';
    });

    closeModalBtn.addEventListener('click', () => {
      loginModal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
      if (e.target === loginModal) {
        loginModal.style.display = 'none';
      }
    });
  }

  // REGISTER 
  if (registerModal) {
    const openRegister = document.getElementById('openRegister');

    if (openRegister) {
      openRegister.addEventListener('click', (e) => {
        e.preventDefault();
        registerModal.style.display = 'flex';
      });
    }

    if (closeRegister) {
      closeRegister.addEventListener('click', () => {
        registerModal.style.display = 'none';
      });
    }

    window.addEventListener('click', (e) => {
      if (e.target === registerModal) {
        registerModal.style.display = 'none';
      }
    });

    if (switchToLogin && loginModal) {
      switchToLogin.addEventListener('click', (e) => {
        e.preventDefault();
        registerModal.style.display = 'none';
        loginModal.style.display = 'flex';
      });
    }
  }
});