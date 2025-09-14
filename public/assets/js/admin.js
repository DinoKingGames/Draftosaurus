const API_BASE = '/api.php/admin';
const USER_ID = (typeof window !== 'undefined' && window.ADMIN_USER_ID) ? window.ADMIN_USER_ID : 0;

let selectedRole = 'user';
let currentUser = null;
let roles = [];

let changeRoleBtn, btnText, btnLoading, messageDiv, currentRoleDiv, userNameDiv, userAvatarDiv, historyContainer, roleOptions;

let userListEl;

document.addEventListener('DOMContentLoaded', () => {
  changeRoleBtn = document.getElementById('change-role-btn');
  btnText = document.getElementById('btn-text');
  btnLoading = document.getElementById('btn-loading');
  messageDiv = document.getElementById('message');
  currentRoleDiv = document.getElementById('current-role');
  userNameDiv = document.getElementById('user-name');
  userAvatarDiv = document.getElementById('user-avatar');
  historyContainer = document.getElementById('history-container');
  roleOptions = document.querySelectorAll('.role-option');
  userListEl = document.getElementById('admin-user-list');

  AdminUsers.init({
    listEl: userListEl,
    onSelected: (u) => {
      if (userNameDiv) userNameDiv.textContent = u.nombre || u.name || '';
      if (currentRoleDiv) {
        const rol = u.rol || u.role || '';
        currentRoleDiv.textContent = rol;
        currentRoleDiv.className = 'user-role ' + rol;
      }
      if (userAvatarDiv) userAvatarDiv.textContent = (u._avatar || 'U');
      if (u.id) window.ADMIN_USER_ID = u.id;
    }
  });

  loadInitialData().then(updateUI);
  setupEventListeners();
  loadHistory();
});

async function loadInitialData() {
  try {
    const uid = (typeof window !== 'undefined' && window.ADMIN_USER_ID) ? window.ADMIN_USER_ID : USER_ID;
    if (!uid) return;
    const userRes = await fetch(`${API_BASE}/get_user?id=${uid}`);
    if (!userRes.ok) throw new Error('Error al cargar usuario');
    currentUser = await userRes.json();

    const rolesRes = await fetch(`${API_BASE}/get_roles`);
    if (!rolesRes.ok) throw new Error('Error al cargar roles');
    roles = await rolesRes.json();
  } catch (e) {
    showMessage('Error al conectar con el backend: ' + e.message, true);
  }
}

function setupEventListeners() {
  if (roleOptions && roleOptions.forEach) {
    roleOptions.forEach(opt => {
      opt.addEventListener('click', function () {
        roleOptions.forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        selectedRole = this.getAttribute('data-role');
      });
    });
  }
  if (changeRoleBtn) changeRoleBtn.addEventListener('click', async () => {
    const selId = AdminUsers.getSelectedId();
    if (!selId) return showMessage('Selecciona un usuario primero', true);
    if (!selectedRole) return showMessage('Selecciona un rol primero', true);

    setLoading(true);
    try {
      const res = await fetch(`${API_BASE}/update_role`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: selId, new_role: selectedRole })
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Error al cambiar el rol');

      await AdminUsers.reload();
      await loadInitialData();
      updateUI();
      await loadHistory();
      showMessage(`¡Cambiaste el rol a ${getRoleName(selectedRole)}!`);
    } catch (e) {
      showMessage(e.message, true);
    } finally {
      setLoading(false);
    }
  });

  const deleteBtn = document.getElementById('btn-eliminar');
  if (deleteBtn) deleteBtn.addEventListener('click', AdminUsers.deleteSelected);
}

async function loadHistory() {
  try {
    const uid = AdminUsers.getSelectedId() || USER_ID;
    if (!uid) return;
    const res = await fetch(`${API_BASE}/get_role_history?user_id=${uid}`);
    const data = await res.json();
    renderHistory(Array.isArray(data) ? data : []);
  } catch {}
}

function renderHistory(items) {
  if (!historyContainer) return;
  historyContainer.innerHTML = '';
  if (!items.length) {
    historyContainer.innerHTML = '<div class="history-item">Sin movimientos</div>';
    return;
  }
  items.forEach(h => {
    const el = document.createElement('div');
    el.className = 'history-item';
    el.innerHTML = `<strong>${getRoleName(h.previous_role)} → ${getRoleName(h.new_role)}</strong>
                    <div class="history-time">${new Date(h.changed_at).toLocaleString()}</div>`;
    historyContainer.appendChild(el);
  });
}

function updateUI() {
  if (!currentUser) return;
  if (userNameDiv) userNameDiv.textContent = currentUser.name;
  if (userAvatarDiv) userAvatarDiv.textContent = currentUser.avatar || 'U';

  if (currentRoleDiv) {
    currentRoleDiv.textContent = getRoleName(currentUser.role);
    currentRoleDiv.className = 'user-role ' + currentUser.role;
  }

  if (roleOptions && roleOptions.forEach) {
    roleOptions.forEach(opt => {
      opt.classList.toggle('selected', opt.getAttribute('data-role') === currentUser.role);
    });
  }
  selectedRole = currentUser.role;
}

function getRoleName(roleId) {
  const r = roles.find(r => r.id === roleId);
  return r ? r.name : roleId;
}

function showMessage(text, isError = false) {
  if (!messageDiv) return;
  messageDiv.textContent = text;
  messageDiv.className = isError ? 'message error' : 'message';
  messageDiv.style.display = 'block';
  setTimeout(() => { if (messageDiv) messageDiv.style.display = 'none'; }, 3000);
}

function setLoading(loading) {
  if (changeRoleBtn) changeRoleBtn.disabled = loading;
  if (btnText) btnText.style.display = loading ? 'none' : 'inline-block';
  if (btnLoading) btnLoading.style.display = loading ? 'inline-block' : 'none';
}

(() => {
  const CFG = {
    base: '/api.php/admin',
    listUrl: '/users',
    getUrl: '/get_user',
    delUrl: '/delete_user',
    roleUrl: '/update_role',
    credentials: 'include',
  };

  const state = {
    listEl: null,
    users: [],
    selectedId: 0,
    onSelected: null,
  };

  function makeUrl(path, params = {}) {
    const u = new URL(CFG.base + path, window.location.origin);
    Object.entries(params).forEach(([k, v]) => u.searchParams.set(k, v));
    return u.toString();
  }

  async function apiGet(path, params = {}) {
    const res = await fetch(makeUrl(path, params), { credentials: CFG.credentials, headers: { 'Accept': 'application/json' } });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || 'Error de API');
    return data;
  }
  async function apiPost(path, body) {
    const res = await fetch(makeUrl(path), {
      method: 'POST',
      credentials: CFG.credentials,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(body || {})
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || 'Error de API');
    return data;
  }

  function initialsFromName(name) {
    const parts = String(name || '').trim().split(/\s+/);
    const ini = [parts[0]?.[0] || '', parts[1]?.[0] || ''].join('');
    return ini.toUpperCase() || 'U';
  }

  function renderList() {
    if (!state.listEl) return;
    const wrap = state.listEl;
    wrap.innerHTML = '';

    if (!state.users.length) {
      const empty = document.createElement('div');
      empty.className = 'user-info';
      empty.innerHTML = `
        <div class="avatar">U</div>
        <div class="user-details">
          <div class="user-name">No hay usuarios</div>
          <div class="user-role user">–</div>
        </div>`;
      wrap.appendChild(empty);
      return;
    }

    state.users.forEach(u => {
      const item = document.createElement('div');
      item.className = 'user-info';
      item.style.cursor = 'pointer';
      item.style.marginBottom = '10px';
      item.dataset.userId = String(u.id);

      const avatar = initialsFromName(u.nombre || u.name);
      const rol = u.rol || u.role || '';

      item.innerHTML = `
        <div class="avatar">${avatar}</div>
        <div class="user-details">
          <div class="user-name">${escapeHtml(u.nombre || u.name || '')}</div>
          <div class="user-role ${rol}">${escapeHtml(rol)}</div>
          <div style="font-size:12px; color:#64748b; margin-top:4px;">${escapeHtml(u.email || '')}</div>
        </div>
      `;

      item.addEventListener('click', () => selectUser(u.id));
      if (u.id === state.selectedId) {
        item.style.boxShadow = '0 0 0 2px #4b6cb7 inset';
        item.style.borderRadius = '10px';
      }
      wrap.appendChild(item);
    });
  }

  async function loadUsers() {
    const data = await apiGet(CFG.listUrl);
    state.users = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
    renderList();
  }

  async function selectUser(id) {
    try {
      const u = await apiGet(CFG.getUrl, { id });
      state.selectedId = u.id;
      u._avatar = initialsFromName(u.nombre || u.name);
      renderList();
      if (typeof state.onSelected === 'function') state.onSelected(u);
    } catch (e) {
      console.error(e);
      alert(e.message || 'No se pudo cargar el usuario');
    }
  }

  async function changeRole(role) {
    if (!state.selectedId) return alert('Seleccioná un usuario');
    if (!role) return alert('Seleccioná un rol');
    try {
      await apiPost(CFG.roleUrl, { user_id: state.selectedId, new_role: role });
      await selectUser(state.selectedId);
      await loadUsers();
      toast('Rol actualizado');
    } catch (e) {
      console.error(e);
      alert(e.message || 'No se pudo actualizar el rol');
    }
  }

  async function deleteSelected() {
    if (!state.selectedId) return alert('Seleccioná un usuario');
    if (!confirm('¿Eliminar este usuario?')) return;
    try {
      await apiPost(CFG.delUrl, { id: state.selectedId });
      state.selectedId = 0;
      await loadUsers();
      toast('Usuario eliminado');
    } catch (e) {
      console.error(e);
      alert(e.message || 'No se pudo eliminar');
    }
  }

  function toast(msg) {
    const box = document.getElementById('message');
    if (!box) return alert(msg);
    box.textContent = msg;
    box.className = 'message';
    box.style.display = 'block';
    setTimeout(() => { box.style.display = 'none'; }, 2000);
  }

  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }

  window.AdminUsers = {
    init({ listEl, onSelected, endpoints } = {}) {
      state.listEl = listEl || document.getElementById('admin-user-list');
      state.onSelected = onSelected || null;
      if (endpoints && typeof endpoints === 'object') Object.assign(CFG, endpoints);
      if (state.listEl) {
        state.listEl.innerHTML = `
          <div class="user-info">
            <div class="avatar">JS</div>
            <div class="user-details">
              <div class="user-name">Aca iría una lista de todos los usuarios</div>
              <div class="user-role user">Cargando...</div>
            </div>
          </div>`;
      }
      loadUsers().catch(err => console.error(err));
    },
    reload: loadUsers,
    getSelectedId: () => state.selectedId,
    changeRole,
    deleteSelected,
    setConfig: (overrides) => Object.assign(CFG, overrides),
  };
})();