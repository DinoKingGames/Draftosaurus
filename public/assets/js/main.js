/* Dinosaurios: 
Rojo: 0
Cyan: 1
Naranja: 2
Rosa: 3
Verde: 4
Azul: 5
*/

/* Config (seguimiento local) */
let dinosaurios = ['rojo','cyan','naranja','rosa','verde','azul'];
let seleccion = null;
let totalColocados = 0;
let totalPorColor = [0,0,0,0,0,0];
let campoUnoColor, campoDosColor, campoTresColor, campoCuatroColor, campoCincoColor, campoSeisColor, campoSieteColor; 
let [campoUnoCantidad, campoDosCantidad, campoTresCantidad, campoCuatroCantidad, campoCincoCantidad, campoSeisCantidad, campoSieteCantidad] = [0, 0, 0, 0, 0, 0, 0];
let campoCincoUsados = [false,false,false,false,false,false];
let campoColores = { 1: [], 2: [], 3: [], 4: [], 5: [], 6: [], 7: [] };
let [scoreUno, scoreDos, scoreTres, scoreCuatro, scoreCinco, scoreSeis, scoreSiete] = [0, 0, 0, 0, 0, 0];

const RECINTO_MAP = {
  1: "El Bosque de la Semejanza",
  2: "El Rio",
  3: "El Trío Frondoso",
  4: "El Rey de la Selva",
  5: "El Prado de la Diferencia",
  6: "La Pradera del Amor",
  7: "La Isla Solitaria",
};
const NAME_TO_ZONE = Object.fromEntries(Object.entries(RECINTO_MAP).map(([id, name]) => [name, Number(id)]));

/* Mapeo especie -> imagen (relleno dinámico desde las manos; con alias de respaldo) */
const SPECIES_IMG = Object.create(null);
const SPECIES_ALIAS = {
  "Rosado": "rosa",
  "Rosa": "rosa",
  "Cyan": "cyan",
  "Azul": "azul",
  "Rojo": "rojo",
  "Naranja": "naranja",
  "Verde": "verde",
  "T-Rex": "trex" // por si aparece; debe existir /imgs/minis/trex.png
};

/* Bases */
const IMG_BASE = (() => {
  try { return new URL('../imgs/', document.currentScript.src).href; }
  catch { return '/assets/imgs/'; }
})();
const API_URL = new URL(window.location.href);

/* Multi-jugador/persistencia */
const USER_ID = (typeof window !== 'undefined' && window.GAME_USER_ID) ? window.GAME_USER_ID : 0;
const STORAGE_KEY = 'drafto_game_id';
let gameId = Number(localStorage.getItem(STORAGE_KEY) || 0);

/* Contador (seguimiento local + marcador online) */
function terminarPartida(){
  if (totalColocados == 6){
    const puntajeFinal = scoreUno + scoreDos + scoreTres + scoreCuatro + scoreCinco + scoreSeis + scoreSiete;
    mostrarMensaje(`El puntaje final es de: ${puntajeFinal}`);
  }
}
function mostrarMensaje(texto) {
  const cont = document.getElementById('mensaje');
  if (cont) cont.textContent = texto;
}
/* Marcador desde backend (si viene en respuestas) */
function setScoresFrom(data) {
  const s1 = document.getElementById('score-1');
  const s2 = document.getElementById('score-2');
  if (!s1 || !s2) return;
  if (data?.scores) {
    if (data.scores[1] != null) s1.textContent = String(data.scores[1]);
    if (data.scores[2] != null) s2.textContent = String(data.scores[2]);
  } else if (data?.game?.placed_count) {
    if (data.game.placed_count[1] != null) s1.textContent = String(data.game.placed_count[1]);
    if (data.game.placed_count[2] != null) s2.textContent = String(data.game.placed_count[2]);
  } else if (data?.placed_count) {
    if (data.placed_count[1] != null) s1.textContent = String(data.placed_count[1]);
    if (data.placed_count[2] != null) s2.textContent = String(data.placed_count[2]);
  }
}

/* Campos (seguimiento local) */
function selectUno() {
  if (seleccion === null) return mostrarMensaje('Selecciona un dinosaurio');
  if (campoUnoColor === undefined) campoUnoColor = seleccion;
  const puntos = [0,2,4,8,12,18,24];
  if (seleccion !== campoUnoColor) return mostrarMensaje('No se acepta');
  if (campoUnoCantidad >= 6) return mostrarMensaje('Máximo 6 dinosaurios');
  campoUnoCantidad++;
  campoColores[1].push(seleccion);
  totalPorColor[seleccion]++;
  scoreUno = puntos[campoUnoCantidad];
  totalColocados++;
  actualizarMostrar(1, seleccion);
  terminarPartida();
}
function selectDos() {
  if (seleccion === null) return mostrarMensaje('Selecciona un dinosaurio');
  campoDosCantidad++;
  campoColores[2].push(seleccion);
  totalPorColor[seleccion]++;
  scoreDos = campoDosCantidad;
  totalColocados++;
  actualizarMostrar(2, seleccion);
  terminarPartida();
}
function selectTres() {
  if (seleccion === null) return mostrarMensaje('Selecciona un dinosaurio');
  if (campoTresCantidad >= 1) return mostrarMensaje('Solo se permite ingresar un dinosaurio');
  campoTresColor = seleccion;
  campoTresCantidad++;
  campoColores[3].push(seleccion);
  totalPorColor[seleccion]++;
  let max = 0;
  for (let i = 0; i < totalPorColor.length; i++) if (totalPorColor[i] > max) max = totalPorColor[i];
  scoreTres = (totalPorColor[campoTresColor] === max) ? 7 : 0;
  totalColocados++;
  actualizarMostrar(3, seleccion);
  terminarPartida();
}
function selectCuatro() {
  if (seleccion === null) return mostrarMensaje('Selecciona un dinosaurio');
  campoCuatroCantidad++;
  campoColores[4].push(seleccion);
  totalPorColor[seleccion]++;
  scoreCuatro = (campoCuatroCantidad === 3) ? 7 : 0;
  totalColocados++;
  actualizarMostrar(4, seleccion);
  terminarPartida();
}
function selectCinco() {
  if (seleccion === null) return mostrarMensaje('Selecciona un dinosaurio');
  if (campoCincoUsados[seleccion]) return mostrarMensaje('No puedes tener 2 dinosaurios del mismo color');
  campoCincoUsados[seleccion] = true;
  campoCincoCantidad++;
  campoColores[5].push(seleccion);
  totalPorColor[seleccion]++;
  const puntos5 = [0,1,3,6,10,15,21];
  scoreCinco = puntos5[campoCincoCantidad];
  totalColocados++;
  actualizarMostrar(5, seleccion);
  terminarPartida();
}
function selectSeis() {
  if (seleccion === null) return mostrarMensaje('Selecciona un dinosaurio');
  campoSeisCantidad++;
  campoColores[6].push(seleccion);
  totalPorColor[seleccion]++;
  let pares = 0;
  for (let i = 0; i < totalPorColor.length; i++) pares += Math.floor(totalPorColor[i]/2);
  scoreSeis = pares * 5;
  totalColocados++;
  actualizarMostrar(6, seleccion);
  terminarPartida();
}
function selectSiete() {
  if (seleccion === null) return mostrarMensaje('Selecciona un dinosaurio');
  if (campoSieteCantidad >= 1) return mostrarMensaje('Solo se permite un dinosaurio aquí');
  campoSieteCantidad++;
  campoColores[7].push(seleccion);
  totalPorColor[seleccion]++;
  scoreSiete = 7;
  totalColocados++;
  actualizarMostrar(7, seleccion);
  terminarPartida();
}

/* Selección (seguimiento local) */
function slctRojo() { seleccion = 0; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-rojo'); if (el) el.classList.add('cambio'); }
function slctCyan() { seleccion = 1; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-cyan'); if (el) el.classList.add('cambio'); }
function slctNaranja() { seleccion = 2; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-naranja'); if (el) el.classList.add('cambio'); }
function slctRosa() { seleccion = 3; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-rosa'); if (el) el.classList.add('cambio'); }
function slctVerde() { seleccion = 4; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-verde'); if (el) el.classList.add('cambio'); }
function slctAzul() { seleccion = 5; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-azul'); if (el) el.classList.add('cambio'); }

/* UI (seguimiento local) */
function actualizarMostrar(seccion, color) {
  const btn = document.getElementById(`btn${seccion}`);
  if (!btn) return;
  const img = document.createElement('img');
  img.src = `${IMG_BASE}minis/${dinosaurios[color]}.png`;
  img.classList.add('mini-dino');
  btn.appendChild(img);
}

/* Helpers de imágenes */
function imageForTipo(tipo) {
  if (!tipo) return `${IMG_BASE}minis/placeholder.png`;
  if (SPECIES_IMG[tipo]) return SPECIES_IMG[tipo];
  const alias = SPECIES_ALIAS[tipo] || tipo;
  return `${IMG_BASE}minis/${String(alias).toLowerCase()}.png`;
}
function updateSpeciesMapFromHand(hand) {
  (hand || []).forEach(d => {
    if (d?.tipo && d?.imagen && !SPECIES_IMG[d.tipo]) {
      SPECIES_IMG[d.tipo] = d.imagen;
    }
  });
}

/* Render/hidratación de tablero desde estado */
function clearBoardSlotsForPlayer(player) {
  const boardEl = document.querySelector(`.contenedor-juego[data-player="${player}"]`);
  if (!boardEl) return;
  boardEl.querySelectorAll('.slot').forEach(s => {
    s.classList.remove('filled');
    while (s.firstChild) s.removeChild(s.firstChild);
  });
}
function hydrateFromState(game) {
  if (!game?.boards) return;
  // Limpiar
  clearBoardSlotsForPlayer(1);
  clearBoardSlotsForPlayer(2);

  for (const p of [1, 2]) {
    const boards = game.boards?.[p] || {};
    const playerEl = document.querySelector(`.contenedor-juego[data-player="${p}"]`);
    if (!playerEl) continue;

    for (const [recintoName, dinos] of Object.entries(boards)) {
      const zoneId = NAME_TO_ZONE[recintoName];
      if (!zoneId) continue;

      const zoneEl = playerEl.querySelector(`.dropzone[data-zone-id="${String(zoneId)}"]`);
      if (!zoneEl) continue;

      // slots disponibles en la zona
      const slots = Array.from(zoneEl.querySelectorAll('.slot'));
      // el Río (id 2) se llena de abajo hacia arriba (igual que en drop)
      const slotIterator = (zoneId === 2) ? [...slots].reverse() : slots;

      let i = 0;
      for (const species of (dinos || [])) {
        const targetSlot = slotIterator.find(s => !s.classList.contains('filled'));
        if (!targetSlot) break;
        const img = document.createElement('img');
        img.src = imageForTipo(species);
        img.alt = species || 'dino';
        img.className = 'dino';
        img.draggable = false;
        targetSlot.classList.add('filled');
        targetSlot.appendChild(img);
        i++;
      }
    }
  }
}

/* Inicio + Multi-jugador */
document.addEventListener('DOMContentLoaded', async () => {
  // Estado de UI (multijugador)
  let currentPlayer = 1;
  let isSwitching = false;

  function showPlayer(p) {
    document.querySelectorAll('.contenedor-juego').forEach(el => {
      const isCurrent = Number(el.dataset.player) === p;
      el.classList.toggle('hidden', !isCurrent);
    });
    currentPlayer = p;
  }
  function normPlayer(n, fallback) {
    const v = Number(n);
    return v === 1 || v === 2 ? v : (fallback === 1 ? 2 : 1);
  }
  function switchPlayerAnimated(nextPlayer) {
    nextPlayer = normPlayer(nextPlayer, currentPlayer);
    if (nextPlayer === currentPlayer) { showPlayer(currentPlayer); return; }
    const currentEl = document.querySelector(`.contenedor-juego[data-player="${currentPlayer}"]`);
    const nextEl = document.querySelector(`.contenedor-juego[data-player="${nextPlayer}"]`);
    if (!currentEl || !nextEl) { showPlayer(nextPlayer); return; }
    nextEl.classList.remove('hidden');
    nextEl.classList.add('anim-in');
    requestAnimationFrame(() => {
      currentEl.classList.add('anim-out');
      setTimeout(() => {
        currentEl.classList.remove('anim-out');
        currentEl.classList.add('hidden');
        nextEl.classList.remove('anim-in');
        currentPlayer = nextPlayer;
      }, 320);
    });
  }

  // API con user_id + game_id (opción para ignorar game_id)
  async function api(action, params = {}, options = {}) {
    const { ignoreGameId = false } = options;
    const isGet = action === 'init' || action === 'get_hand' || action === 'state' || action === 'load';
    const base = { action, user_id: USER_ID };
    if (gameId && !ignoreGameId) base.game_id = gameId;

    if (isGet) {
      const url = new URL(API_URL);
      Object.entries({ ...base, ...params }).forEach(([k, v]) => url.searchParams.set(k, v));
      const res = await fetch(url.toString(), {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'include',
        cache: 'no-store',
      });
      return res.json();
    } else {
      const body = new URLSearchParams({ ...base, ...params });
      const res = await fetch(API_URL.toString(), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json' },
        body,
        credentials: 'include',
        cache: 'no-store',
      });
      return res.json();
    }
  }

  // Drag & Drop
  function makeDraggable(img) {
    img.setAttribute('draggable', 'true');
    if (!img.dataset.dragId) img.dataset.dragId = 'dino-' + (Date.now() + Math.random()).toString(36);
    img.addEventListener('dragstart', (e) => {
      const payload = JSON.stringify({
        src: e.currentTarget.src,
        id: e.currentTarget.dataset.dragId,
        tipo: e.currentTarget.dataset?.tipo || null,
        gameId: e.currentTarget.dataset?.gameId || null,
      });
      e.dataTransfer.setData('text/plain', payload);
      e.dataTransfer.effectAllowed = 'copyMove';
    });
  }
  document.querySelectorAll('.mini-dino').forEach(makeDraggable);

  function renderBandejaFor(player, hand) {
    // actualiza el mapa de especies -> imagen con lo que trae el backend
    updateSpeciesMapFromHand(hand);

    const cont = document.querySelector(`.contenedor-juego[data-player="${player}"] .bandeja .dinosaurios`);
    if (!cont) return;
    cont.innerHTML = '';
    (hand || []).forEach(d => {
      const img = document.createElement('img');
      img.src = d.imagen || imageForTipo(d.tipo);
      img.alt = d.tipo || 'dino';
      img.className = 'mini-dino';
      img.dataset.tipo = d.tipo || '';
      img.dataset.gameId = d.id;
      makeDraggable(img);
      cont.appendChild(img);
    });
  }

  const ZONAS = [
    { id: 1, nombre: 'Bosque Semejanza', x: 0,  y: 2,  w: 38, h: 32, slots: 6, cols: 6 },
    { id: 3, nombre: 'Trío Frondoso',    x: 10, y: 37, w: 23, h: 18, slots: 3, cols: 3 },
    { id: 6, nombre: 'Pradera del Amor', x: 4,  y: 72, w: 18, h: 22, slots: 6, cols: 6 }, 
    { id: 4, nombre: 'Rey de la Selva',  x: 69, y: 9,  w: 15, h: 18, slots: 1, cols: 1 },
    { id: 5, nombre: 'Prado Diferencia', x: 69, y: 46, w: 25, h: 25, slots: 6, cols: 6 },
    { id: 7, nombre: 'Isla Solitaria',   x: 68, y: 78, w: 20, h: 22, slots: 1, cols: 1 },
    { id: 2, nombre: 'Río',              x: 50, y: 0,  w: 8,  h: 100, slots: 8, cols: 1 }, 
  ];

  function renderZonasFor(tablero, player) {
    if (!tablero) return;
    tablero.querySelectorAll('.dropzone').forEach(z => z.remove());

    ZONAS.forEach(z => {
      const el = document.createElement('div');
      el.className = 'dropzone';
      el.style.left = `${z.x}%`;
      el.style.top  = `${z.y}%`;
      el.style.width  = `${z.w}%`;
      el.style.height = `${z.h}%`;
      el.dataset.zoneId = String(z.id);
      el.dataset.player = String(player);

      const slotsWrap = document.createElement('div');
      slotsWrap.className = 'slots';
      const cols = Math.max(1, (z.cols ?? z.slots ?? 1));
      const count = Math.max(1, (z.slots ?? cols));
      slotsWrap.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;

      for (let i = 1; i <= count; i++) {
        const s = document.createElement('div');
        s.className = 'slot';
        s.dataset.slotIndex = String(i);
        slotsWrap.appendChild(s);
      }
      el.appendChild(slotsWrap);

      el.addEventListener('dragover', (e) => {
        e.preventDefault();
        const boardPlayer = Number(el.closest('.contenedor-juego')?.dataset.player || player);
        if (boardPlayer !== currentPlayer) return;
        el.classList.add('is-over');
        if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';
      });
      el.addEventListener('dragleave', () => el.classList.remove('is-over'));

      el.addEventListener('drop', async (e) => {
        e.preventDefault();
        el.classList.remove('is-over');
        if (isSwitching) return;

        const boardPlayer = Number(el.closest('.contenedor-juego')?.dataset.player || player);
        if (boardPlayer !== currentPlayer) {
          alert('No es tu turno');
          return;
        }

        const raw = e.dataTransfer.getData('text/plain');
        if (!raw) return;

        let data;
        try { data = JSON.parse(raw); } catch { data = { src: raw, id: null, tipo: null, gameId: null }; }

        const dinoId = data.gameId;
        if (!dinoId) {
          alert('Usa los dinosaurios de la bandeja (se actualiza desde el servidor).');
          return;
        }

        const zoneId = Number(el.dataset.zoneId);
        const recintoName = RECINTO_MAP[zoneId];
        if (!recintoName) {
          alert('Recinto inválido');
          return;
        }

        let freeSlot;
        if (String(zoneId) === '2') {
          freeSlot = Array.from(el.querySelectorAll('.slot')).reverse().find(s => !s.classList.contains('filled'));
        } else {
          freeSlot = el.querySelector('.slot:not(.filled)');
        }
        if (!freeSlot) return;

        try {
          const r = await api('place', { player: currentPlayer, dino_id: dinoId, recinto: recintoName });
          if (!r?.success) {
            alert(r?.message || 'Error al colocar el dinosaurio');
            return;
          }

          const placed = r.data?.placed_dino;
          if (placed) {
            const img = document.createElement('img');
            img.src = placed.imagen || imageForTipo(placed.tipo);
            img.alt = placed.tipo || 'dino';
            img.className = 'dino';
            img.draggable = false;
            freeSlot.classList.add('filled');
            freeSlot.appendChild(img);
          }

          // Actualizar bandeja y marcador
          renderBandejaFor(currentPlayer, r.data?.new_hand || []);
          setScoresFrom(r.data);

          // Guardar game_id si vino
          if (r.data?.game_id && !gameId) {
            gameId = Number(r.data.game_id);
            localStorage.setItem(STORAGE_KEY, String(gameId));
          }

          // Traer mano del siguiente jugador
          const nextPlayer = normPlayer(r?.data?.game?.current_player, currentPlayer);
          try {
            const handNext = await api('get_hand', { player: nextPlayer });
            if (handNext?.success) {
              renderBandejaFor(nextPlayer, handNext.data?.hand || []);
              setScoresFrom(handNext.data);
            }
          } catch {}

          // Animar cambio de jugador
          isSwitching = true;
          setTimeout(() => {
            const finalNext = normPlayer(r?.data?.game?.current_player, currentPlayer);
            showPlayer(finalNext);
            isSwitching = false;
          }, 350);

          if (r.data?.finished) alert('Partida finalizada');
        } catch (err) {
          console.error(err);
          alert('Error de red al colocar el dinosaurio');
        }
      });

      tablero.appendChild(el);
    });
  }

  // Render inicial de zonas
  document.querySelectorAll('.contenedor-juego').forEach(boardEl => {
    const player = Number(boardEl.dataset.player || '1');
    const tablero = boardEl.querySelector('.tablero');
    renderZonasFor(tablero, player);
  });

  const btnReanudar = document.getElementById('btn-reanudar');
  const btnNueva = document.getElementById('btn-nueva');
  const pantalla = document.getElementById('pantalla-inicio');
  const startErr = document.getElementById('start-error');

  function clearError() {
    if (!startErr) return;
    startErr.textContent = '';
    startErr.classList.add('hidden');
  }
  function showError(msg) {
    if (!startErr) return;
    startErr.textContent = msg;
    startErr.classList.remove('hidden');
  }

  async function cargarManosYScores() {
    for (const p of [1, 2]) {
      const handRes = await api('get_hand', { player: p });
      if (handRes?.success) {
        renderBandejaFor(p, handRes.data?.hand || []);
        setScoresFrom(handRes.data);
      }
    }
  }

  async function resumeGame() {
    clearError();
    if (!USER_ID) {
      showError('Debes iniciar sesión para reanudar una partida.');
      return;
    }
    if (!gameId) {
      showError('No hay una partida guardada para reanudar.');
      return;
    }
    try {
      const r = await api('load');
      if (!r?.success) {
        showError(r?.message || 'No se pudo reanudar. Podés iniciar una partida nueva.');
        return;
      }
      if (pantalla) pantalla.classList.add('hidden');
      showPlayer(1);
      setScoresFrom(r.data);

      // Hidratar tablero desde el estado cargado
      if (r.data?.game) hydrateFromState(r.data.game);

      // Cargar manos (también alimenta el mapa de especies->imagen)
      await cargarManosYScores();
    } catch (e) {
      console.warn('No se pudo cargar partida previa:', e);
      showError('No se pudo reanudar. Probá con “Partida nueva”.');
    }
  }

  async function startNewGame() {
    clearError();
    // limpiar cualquier game_id previo para asegurarnos que iniciamos de cero
    localStorage.removeItem(STORAGE_KEY);
    gameId = 0;
    if (btnNueva) { btnNueva.disabled = true; btnNueva.textContent = 'Iniciando...'; }
    try {
      const initRes = await api('init', {}, { ignoreGameId: true });
      if (!initRes?.success) throw new Error(initRes?.message || 'Error al iniciar');

      const newId = Number(initRes.data?.game_id || 0);
      if (newId) {
        gameId = newId;
        localStorage.setItem(STORAGE_KEY, String(gameId));
      }

      if (pantalla) pantalla.classList.add('hidden');
      showPlayer(1);

      // Tablero nuevo está vacío; manos para empezar
      await cargarManosYScores();
    } catch (e) {
      console.error(e);
      showError('No se pudo iniciar la partida. Intenta nuevamente.');
    } finally {
      if (btnNueva) { btnNueva.disabled = false; btnNueva.textContent = 'Partida nueva'; }
    }
  }

  // Deshabilitar “Reanudar” si no hay user o game_id
  if (btnReanudar) {
    if (!USER_ID || !gameId) {
      btnReanudar.disabled = true;
      btnReanudar.title = !USER_ID ? 'Inicia sesión para reanudar' : 'No hay partida previa para reanudar';
    }
    btnReanudar.addEventListener('click', resumeGame);
  }
  if (btnNueva) {
    btnNueva.addEventListener('click', startNewGame);
  }

  // Nota: Ya NO auto-reanudamos al cargar; el usuario elige.
});