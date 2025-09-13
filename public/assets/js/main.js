/* Dinosaurios: 
Rojo: 0
Cyan: 1
Naranja: 2
Rosa: 3
Verde: 4
Azul: 5
*/

let dinosaurios = ['rojo','cyan','naranja','rosa','verde','azul'];
let seleccion = null;
let totalColocados = 0;
let totalPorColor = [0,0,0,0,0,0];

let campoUnoColor, campoDosColor, campoTresColor, campoCuatroColor, campoCincoColor, campoSeisColor, campoSieteColor; 
let [campoUnoCantidad, campoDosCantidad, campoTresCantidad, campoCuatroCantidad, campoCincoCantidad, campoSeisCantidad, campoSieteCantidad] = [0, 0, 0, 0, 0, 0, 0];

let campoCincoUsados = [false,false,false,false,false,false];

// mantenemos tu contador de colores por sección, pero lo usamos sólo para el score
let campoColores = {
  1: [], 2: [], 3: [], 4: [], 5: [], 6: [], 7: []
};

let [scoreUno, scoreDos, scoreTres, scoreCuatro, scoreCinco, scoreSeis, scoreSiete] = [0, 0, 0, 0, 0, 0, 0];

function terminarPartida(){
    if (totalColocados == 6){
        const puntajeFinal = scoreUno + scoreDos + scoreTres + scoreCuatro + scoreCinco + scoreSeis + scoreSiete;
        mostrarMensaje(`El puntaje final es de: ${puntajeFinal}`);
    }
}

function mostrarMensaje(texto) {
  const cont = document.getElementById('mensaje');
  if (cont) cont.textContent = texto || '';
}

// Agregado: marcador de puntajes (evita ReferenceError y unifica origen)
function setScoresFrom(payload) {
  const s1 = document.getElementById('score-1');
  const s2 = document.getElementById('score-2');
  if (!s1 || !s2) return;

  if (payload?.scores) {
    if (payload.scores[1] != null) s1.textContent = String(payload.scores[1]);
    if (payload.scores[2] != null) s2.textContent = String(payload.scores[2]);
    return;
  }
  if (payload?.data?.scores) {
    if (payload.data.scores[1] != null) s1.textContent = String(payload.data.scores[1]);
    if (payload.data.scores[2] != null) s2.textContent = String(payload.data.scores[2]);
    return;
  }
  const placed = payload?.game?.placed_count || payload?.placed_count;
  if (placed) {
    if (placed[1] != null) s1.textContent = String(placed[1]);
    if (placed[2] != null) s2.textContent = String(placed[2]);
  }
}

/* ===================== HUD: estilos + helpers ===================== */
function injectHudStyles() {
  if (document.getElementById('hud-styles')) return;
  const style = document.createElement('style');
  style.id = 'hud-styles';
  style.textContent = `
  .hud {
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    display: flex;
    gap: 16px;
    align-items: center;
    backdrop-filter: blur(6px);
    background: rgba(20,20,30,0.55);
    color: #fff;
    padding: 10px 14px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji';
  }
  .hud .player-badge {
    background: linear-gradient(135deg, #4e54c8, #8f94fb);
    padding: 6px 10px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: .3px;
    white-space: nowrap;
  }
  .restriction {
    display: flex; align-items: center; gap: 10px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px;
    padding: 6px 10px;
  }
  .restriction .die {
    width: 26px; height: 26px; object-fit: contain;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));
  }
  .restriction .text {
    font-size: 13px;
  }
  .chips {
    display: flex; gap: 6px; flex-wrap: wrap; margin-left: 6px;
  }
  .chip {
    font-size: 11px; padding: 3px 8px; border-radius: 999px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    color: #fff;
  }
  .restriction.forest { box-shadow: inset 0 0 0 1px rgba(46,204,113,.35); }
  .restriction.plain  { box-shadow: inset 0 0 0 1px rgba(241,196,15,.35); }
  .restriction.cafe   { box-shadow: inset 0 0 0 1px rgba(52,152,219,.35); }
  .restriction.bath   { box-shadow: inset 0 0 0 1px rgba(231,76,60,.35); }
  .restriction.empty  { box-shadow: inset 0 0 0 1px rgba(149,165,166,.35); }
  .restriction.trex   { box-shadow: inset 0 0 0 1px rgba(155,89,182,.35); }
  .restriction.neutral { opacity: .8; }
  /* Pequeño facelift a los puntajes existentes si están en el DOM */
  #score-1, #score-2 {
    display: inline-block; min-width: 22px; text-align: center;
    padding: 2px 6px; border-radius: 8px; margin: 0 3px;
    background: rgba(0,0,0,0.2); color: #fff; font-weight: 700;
  }
  `;
  document.head.appendChild(style);
}

function ensureHud() {
  let hud = document.getElementById('hud');
  if (!hud) {
    hud = document.createElement('div');
    hud.id = 'hud';
    hud.className = 'hud';
    const badge = document.createElement('div');
    badge.id = 'player-badge';
    badge.className = 'player-badge';
    badge.textContent = 'Turno de Jugador 1';

    const restriction = document.createElement('div');
    restriction.id = 'restriction-banner';
    restriction.className = 'restriction neutral';
    const img = document.createElement('img');
    img.className = 'die';
    img.alt = 'dado';
    img.draggable = false;
    const span = document.createElement('span');
    span.className = 'text';
    span.textContent = 'Sin restricción';
    const chips = document.createElement('div');
    chips.className = 'chips';
    restriction.appendChild(img);
    restriction.appendChild(span);
    restriction.appendChild(chips);

    hud.appendChild(badge);
    hud.appendChild(restriction);
    document.body.appendChild(hud);
  }
  return hud;
}

const DICE_FACE_ORDER = ['Bosque','Llanura','Cafetería','Baños','Recinto vacío','Zona libre de T‑Rex'];
function diceIndexForFace(face) {
  const i = DICE_FACE_ORDER.indexOf(face || '');
  return i >= 0 ? (i + 1) : 1;
}
function diceSrcByIndex(idx) {
  const n = Math.min(6, Math.max(1, Number(idx) || 1));
  return `${IMG_BASE}dado/dado${n}.png`;
}

function restrictionFaceText(face) {
  switch (face) {
    case 'Bosque': return 'Solo recintos de Bosque (arriba)';
    case 'Llanura': return 'Solo recintos de Llanura (abajo)';
    case 'Cafetería': return 'Solo recintos a la izquierda del río';
    case 'Baños': return 'Solo recintos a la derecha del río';
    case 'Recinto vacío': return 'Solo recintos vacíos';
    case 'Zona libre de T‑Rex': return 'Sin T‑Rex en el recinto';
    default: return 'Sin restricción';
  }
}
function restrictionClassForFace(face) {
  switch (face) {
    case 'Bosque': return 'forest';
    case 'Llanura': return 'plain';
    case 'Cafetería': return 'cafe';
    case 'Baños': return 'bath';
    case 'Recinto vacío': return 'empty';
    case 'Zona libre de T‑Rex': return 'trex';
    default: return 'neutral';
  }
}
function currentRestrictionForPlayer(game, player) {
  const dice = game?.dice || {};
  if (!dice?.face) return null;
  if (Number(dice?.applies_to || 0) !== Number(player)) return null;
  return {
    face: dice.face,
    text: restrictionFaceText(dice.face),
    allowed: Array.isArray(dice.allowed_recintos) ? dice.allowed_recintos : [],
    idx: diceIndexForFace(dice.face)
  };
}
function updatePlayerBadge(game) {
  const badge = document.getElementById('player-badge') || ensureHud().querySelector('#player-badge');
  const cp = Number(game?.current_player || 1);
  if (badge) badge.textContent = `Turno de Jugador ${cp}`;
}
function updateRestrictionBanner(game, player) {
  ensureHud();
  const banner = document.getElementById('restriction-banner');
  if (!banner) return;
  const img = banner.querySelector('.die');
  const text = banner.querySelector('.text');
  const chipsWrap = banner.querySelector('.chips');

  // reset
  banner.className = 'restriction neutral';
  chipsWrap.innerHTML = '';
  if (img) img.src = diceSrcByIndex(1);
  if (text) text.textContent = 'Sin restricción';
  banner.title = '';

  const info = currentRestrictionForPlayer(game, player);
  if (!info) return;

  const cls = restrictionClassForFace(info.face);
  banner.classList.remove('neutral');
  banner.classList.add(cls);
  if (img) img.src = diceSrcByIndex(info.idx);
  if (text) text.textContent = `${info.face}: ${info.text}`;
  if (Array.isArray(info.allowed) && info.allowed.length) {
    const maxChips = 6;
    info.allowed.slice(0, maxChips).forEach(name => {
      const chip = document.createElement('span');
      chip.className = 'chip';
      chip.textContent = name;
      chipsWrap.appendChild(chip);
    });
    if (info.allowed.length > maxChips) {
      const more = document.createElement('span');
      more.className = 'chip';
      more.textContent = `+${info.allowed.length - maxChips}`;
      chipsWrap.appendChild(more);
    }
    banner.title = `Recintos permitidos: ${info.allowed.join(', ')}`;
  }
}

/* ===================== Código de tablero + dado ===================== */

// Mapeos de recintos (alineado al backend; "El Rio" sin tilde)
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

// Normalizador para tolerar tildes y pequeños cambios en nombres
function normalizeName(s) {
  return (s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}
const NAME_TO_ZONE_NORM = Object.fromEntries(
  Object.entries(RECINTO_MAP).map(([id, name]) => [normalizeName(name), Number(id)])
);

// Base de assets
const IMG_BASE = (() => {
  try { return new URL('../imgs/', document.currentScript.src).href; }
  catch { return '/assets/imgs/'; }
})();

// API base (misma URL de la página)
const API_URL = new URL(window.location.href);

// Usuario y almacenamiento local
const USER_ID = (typeof window !== 'undefined' && window.GAME_USER_ID) ? window.GAME_USER_ID : 0;
const STORAGE_KEY = 'drafto_game_id';
let gameId = Number(localStorage.getItem(STORAGE_KEY) || 0);

// Estado global actual del juego
let CURRENT_GAME = null;

// Diccionario especie -> imagen (se completa con manos)
const SPECIES_IMG = Object.create(null);
const SPECIES_ALIAS = {
  "Rosado": "rosa",
  "Rosa": "rosa",
  "Cyan": "cyan",
  "Azul": "azul",
  "Rojo": "rojo",
  "Naranja": "naranja",
  "Verde": "verde",
  "T-Rex": "trex"
};

/* ---------------------- Imágenes dinosaurios ---------------------- */
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

/* ----------------------- Hidratación tablero ----------------------- */
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
  clearBoardSlotsForPlayer(1);
  clearBoardSlotsForPlayer(2);

  for (const p of [1, 2]) {
    const boards = game.boards?.[p] || {};
    const playerEl = document.querySelector(`.contenedor-juego[data-player="${p}"]`);
    if (!playerEl) continue;

    for (const [recintoName, dinos] of Object.entries(boards)) {
      // Resolver zoneId con fallback normalizado
      let zoneId = NAME_TO_ZONE[recintoName];
      if (!zoneId) zoneId = NAME_TO_ZONE_NORM[normalizeName(recintoName)];
      if (!zoneId) {
        console.warn('Recinto no mapeado (se omite en hidratación):', recintoName);
        continue;
      }

      const zoneEl = playerEl.querySelector(`.dropzone[data-zone-id="${String(zoneId)}"]`);
      if (!zoneEl) continue;

      const slots = Array.from(zoneEl.querySelectorAll('.slot'));
      const slotIterator = (zoneId === 2) ? [...slots].reverse() : slots;

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
      }
    }
  }
}

/* -------------------------- Dado (UI) -------------------------- */
function updateDiceUI(game) {
  const d1 = document.getElementById('dice-1');
  const d2 = document.getElementById('dice-2');
  if (!d1 || !d2) return;

  const face = game?.dice?.face || null;
  const appliesTo = Number(game?.dice?.applies_to || 0);
  const idx = diceIndexForFace(face);

  d1.src = diceSrcByIndex(idx);
  d2.src = diceSrcByIndex(idx);

  d1.title = face ? `Dado: ${face}` : 'Dado';
  d2.title = face ? `Dado: ${face}` : 'Dado';

  d1.classList.toggle('inactive', appliesTo !== 1 || !face);
  d2.classList.toggle('inactive', appliesTo !== 2 || !face);
}

// Spin real (~1s) ciclando caras
let isDiceRolling = false;
function startDiceSpin(el) {
  if (!el) return () => {};
  el.classList.add('spin');
  let ticks = 0;
  const maxTicks = 12;
  const timer = setInterval(() => {
    ticks++;
    const randIdx = Math.floor(Math.random() * 6) + 1;
    el.src = diceSrcByIndex(randIdx);
    if (ticks >= maxTicks) {
      clearInterval(timer);
      el.classList.remove('spin');
    }
  }, 80);
  return () => { clearInterval(timer); el.classList.remove('spin'); };
}

/* ----------------- Bloqueo de zonas por dado/turno ----------------- */
function applyDiceLocksForPlayer(game, player) {
  const msg = document.getElementById('mensaje');
  const dice = game?.dice || {};
  const appliesTo = Number(dice?.applies_to || 0);
  const face = dice?.face || null;
  const allowedNames = Array.isArray(dice?.allowed_recintos) ? dice.allowed_recintos : null;
  const turnRolled = !!dice?.turn_rolled;

  // Limpiar
  document.querySelectorAll(`.contenedor-juego[data-player="${player}"] .dropzone`).forEach(z => {
    z.classList.remove('disabled');
    z.removeAttribute('title');
  });

  // Si no se tiró el dado aún en este turno -> bloquear todo
  if (!turnRolled) {
    const playerEl = document.querySelector(`.contenedor-juego[data-player="${player}"]`);
    if (playerEl) {
      playerEl.querySelectorAll('.dropzone').forEach(z => {
        z.classList.add('disabled');
        z.title = 'Primero tirá el dado';
      });
    }
    if (msg) msg.textContent = 'Tirá el dado antes de colocar.';
    return;
  }

  // Si hay una restricción ACTIVA para este jugador
  if (face && appliesTo === player && allowedNames) {
    const allowedSet = new Set(allowedNames);
    const playerEl = document.querySelector(`.contenedor-juego[data-player="${player}"]`);
    if (playerEl) {
      playerEl.querySelectorAll('.dropzone').forEach(z => {
        const zoneId = Number(z.dataset.zoneId);
        const recintoName = RECINTO_MAP[zoneId];
        if (!allowedSet.has(recintoName)) {
          z.classList.add('disabled');
          z.title = `Restringido por dado: ${face}`;
        }
      });
    }
    if (msg) msg.textContent = `Restricción por dado: ${face}`;
    return;
  }

  // Sin restricciones visibles
  if (msg) msg.textContent = '';
}

/* --------------------------- API helper --------------------------- */
async function api(action, params = {}, options = {}) {
  const { ignoreGameId = false } = options;
  const isGet = ['init','get_hand','state','load','roll'].includes(action);
  const base = { action, user_id: USER_ID };
  if (gameId && !ignoreGameId) base.game_id = gameId;

  try {
    if (isGet) {
      const url = new URL(API_URL);
      Object.entries({ ...base, ...params }).forEach(([k, v]) => url.searchParams.set(k, v));
      const res = await fetch(url.toString(), {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'include',
        cache: 'no-store',
      });
      const text = await res.text();
      try { return JSON.parse(text); } catch { return { success: false, code: res.status, message: 'Respuesta inválida del servidor', raw: text }; }
    } else {
      const body = new URLSearchParams({ ...base, ...params });
      const res = await fetch(API_URL.toString(), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json' },
        body,
        credentials: 'include',
        cache: 'no-store',
      });
      const text = await res.text();
      try { return JSON.parse(text); } catch { return { success: false, code: res.status, message: 'Respuesta inválida del servidor', raw: text }; }
    }
  } catch (e) {
    console.error('API error', e);
    return { success: false, code: -1, message: 'Error de red' };
  }
}

// Resync helper tras errores
async function syncStateAndRefresh() {
  const s = await api('state');
  if (s?.success && s.data?.game) {
    CURRENT_GAME = s.data.game;
    const cp = Number(CURRENT_GAME.current_player || 1);
    updateDiceUI(CURRENT_GAME);
    applyDiceLocksForPlayer(CURRENT_GAME, cp);
    updatePlayerBadge(CURRENT_GAME);
    updateRestrictionBanner(CURRENT_GAME, cp);
  }
  return s;
}

/* ------------------------- Drag & Drop + UI ------------------------- */
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

function renderBandejaFor(player, hand) {
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

// Zonas del tablero
const ZONAS = [
  { id: 1, nombre: 'Bosque Semejanza', x: 0,  y: 2,  w: 38, h: 32, slots: 6, cols: 6 },
  { id: 3, nombre: 'Trío Frondoso',    x: 10, y: 37, w: 23, h: 18, slots: 3, cols: 3 },
  { id: 6, nombre: 'Pradera del Amor', x: 4,  y: 72, w: 18, h: 22, slots: 6, cols: 6 }, 
  { id: 4, nombre: 'Rey de la Selva',  x: 69, y: 9,  w: 15, h: 18, slots: 1, cols: 1 },
  { id: 5, nombre: 'Prado Diferencia', x: 69, y: 46, w: 25, h: 25, slots: 6, cols: 6 },
  { id: 7, nombre: 'Isla Solitaria',   x: 68, y: 78, w: 20, h: 22, slots: 1, cols: 1 },
  { id: 2, nombre: 'Río',              x: 50, y: 0,  w: 8,  h: 100, slots: 8, cols: 1 }, 
];

function renderZonasFor(tablero, player, getCurrentPlayer, showPlayerCb) {
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
    el.dataset.recintoName = RECINTO_MAP[z.id] || '';

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
      if (boardPlayer !== getCurrentPlayer()) return;
      if (el.classList.contains('disabled')) return;
      el.classList.add('is-over');
      if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';
    });

    el.addEventListener('dragleave', () => el.classList.remove('is-over'));

    el.addEventListener('drop', async (e) => {
      e.preventDefault();
      el.classList.remove('is-over');

      const currentPlayer = getCurrentPlayer();
      const boardPlayer = Number(el.closest('.contenedor-juego')?.dataset.player || player);
      if (boardPlayer !== currentPlayer) {
        alert('No es tu turno');
        return;
      }

      // Si no se tiró el dado, no intentes colocar
      if (!CURRENT_GAME?.dice?.turn_rolled) {
        alert('Primero tirá el dado');
        return;
      }

      if (el.classList.contains('disabled')) {
        const face = CURRENT_GAME?.dice?.face || 'dado';
        alert(el.title || `Movimiento no permitido (${face})`);
        return;
      }

      const raw = e.dataTransfer.getData('text/plain');
      if (!raw) return;

      let data;
      try { data = JSON.parse(raw); } catch { data = { src: raw, id: null, tipo: null, gameId: null }; }

      const dinoId = data.gameId;
      if (!dinoId) {
        alert('Usa los dinosaurios de la bandeja.');
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
          if (r?.data?.game) {
            CURRENT_GAME = r.data.game;
            const cp = Number(CURRENT_GAME.current_player || currentPlayer);
            updateDiceUI(CURRENT_GAME);
            applyDiceLocksForPlayer(CURRENT_GAME, cp);
            updatePlayerBadge(CURRENT_GAME);
            updateRestrictionBanner(CURRENT_GAME, cp);
          } else {
            await syncStateAndRefresh();
          }
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

        // Actualizar bandeja, marcador, estado y bloqueos de dado
        renderBandejaFor(currentPlayer, r.data?.new_hand || []);
        setScoresFrom(r.data);
        if (r.data?.game) {
          CURRENT_GAME = r.data.game;
          applyDiceLocksForPlayer(CURRENT_GAME, CURRENT_GAME.current_player || currentPlayer);
          updateDiceUI(CURRENT_GAME);
          updatePlayerBadge(CURRENT_GAME);
          updateRestrictionBanner(CURRENT_GAME, CURRENT_GAME.current_player || currentPlayer);
        } else {
          await syncStateAndRefresh();
        }

        // Guardar game_id si vino
        if (r.data?.game_id && !gameId) {
          gameId = Number(r.data.game_id);
          localStorage.setItem(STORAGE_KEY, String(gameId));
        }

        // Traer mano del siguiente jugador
        const nextPlayer = Number(r?.data?.game?.current_player || (currentPlayer === 1 ? 2 : 1));
        try {
          const handNext = await api('get_hand', { player: nextPlayer });
          if (handNext?.success) {
            renderBandejaFor(nextPlayer, handNext.data?.hand || []);
            setScoresFrom(handNext.data);
            if (handNext.data?.game) {
              CURRENT_GAME = handNext.data.game;
              applyDiceLocksForPlayer(CURRENT_GAME, CURRENT_GAME.current_player || nextPlayer);
              updateDiceUI(CURRENT_GAME);
              updatePlayerBadge(CURRENT_GAME);
              updateRestrictionBanner(CURRENT_GAME, CURRENT_GAME.current_player || nextPlayer);
            }
          }
        } catch {}

        // Cambiar visualmente de jugador (usa el callback del cierre correcto)
        showPlayerCb(nextPlayer);

        if (r.data?.finished) alert('Partida finalizada');
      } catch (err) {
        console.error(err);
        alert('Error de red al colocar el dinosaurio');
        await syncStateAndRefresh();
      }
    });

    tablero.appendChild(el);
  });
}

/* ------------------------ DOMContentLoaded ------------------------ */
document.addEventListener('DOMContentLoaded', () => {
  injectHudStyles();
  ensureHud();

  let currentPlayer = 1;

  function getCurrentPlayer() {
    return currentPlayer;
  }

  function showPlayer(p) {
    document.querySelectorAll('.contenedor-juego').forEach(el => {
      const isCurrent = Number(el.dataset.player) === p;
      el.classList.toggle('hidden', !isCurrent);
    });
    // Si tenés dos dados visibles, podés alternar su estado con este selector
    document.querySelectorAll('.dice-img').forEach(img => {
      img.classList.toggle('inactive', Number(img.dataset.player) !== p);
    });

    currentPlayer = p;
    if (CURRENT_GAME) {
      applyDiceLocksForPlayer(CURRENT_GAME, currentPlayer);
      updateDiceUI(CURRENT_GAME);
      updatePlayerBadge(CURRENT_GAME);
      updateRestrictionBanner(CURRENT_GAME, currentPlayer);
    } else {
      updatePlayerBadge({ current_player: currentPlayer });
      updateRestrictionBanner(null, currentPlayer);
    }
  }

  // Render inicial de zonas (ambos tableros) pasando los callbacks correctos
  document.querySelectorAll('.contenedor-juego').forEach(boardEl => {
    const player = Number(boardEl.dataset.player || '1');
    const tablero = boardEl.querySelector('.tablero');
    renderZonasFor(tablero, player, getCurrentPlayer, showPlayer);
  });

  // Botones inicio
  const btnReanudar = document.getElementById('btn-reanudar');
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
        if (handRes.data?.game) CURRENT_GAME = handRes.data.game;
      }
    }
    if (CURRENT_GAME) {
      hydrateFromState(CURRENT_GAME);
      applyDiceLocksForPlayer(CURRENT_GAME, CURRENT_GAME.current_player || 1);
      updateDiceUI(CURRENT_GAME);
      updatePlayerBadge(CURRENT_GAME);
      updateRestrictionBanner(CURRENT_GAME, CURRENT_GAME.current_player || 1);
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
      if (r.data?.game) CURRENT_GAME = r.data.game;
      pantalla?.classList.add('hidden');
      showPlayer(1);
      setScoresFrom(r.data);

      if (CURRENT_GAME) {
        hydrateFromState(CURRENT_GAME);
        updateDiceUI(CURRENT_GAME);
        updatePlayerBadge(CURRENT_GAME);
        updateRestrictionBanner(CURRENT_GAME, CURRENT_GAME.current_player || 1);
      }
      await cargarManosYScores();
    } catch (e) {
      console.warn('No se pudo cargar partida previa:', e);
      showError('No se pudo reanudar. Probá con “Partida nueva”.');
    }
  }

  async function startNewGame() {
    clearError();
    localStorage.removeItem(STORAGE_KEY);
    gameId = 0;

    const btnNuevaEl = document.getElementById('btn-nueva');
    if (btnNuevaEl) { btnNuevaEl.disabled = true; btnNuevaEl.textContent = 'Iniciando...'; }

    try {
      const initRes = await api('init', {}, { ignoreGameId: true });
      if (!initRes?.success) throw new Error(initRes?.message || 'Error al iniciar');

      const newId = Number(initRes.data?.game_id || 0);
      if (newId) {
        gameId = newId;
        localStorage.setItem(STORAGE_KEY, String(gameId));
      }

      if (initRes.data?.game) CURRENT_GAME = initRes.data.game;
      pantalla?.classList.add('hidden');
      showPlayer(1);

      await cargarManosYScores();
    } catch (e) {
      console.error(e);
      showError('No se pudo iniciar la partida. Intenta nuevamente.');
    } finally {
      if (btnNuevaEl) { btnNuevaEl.disabled = false; btnNuevaEl.textContent = 'Partida nueva'; }
    }
  }

  // Dado: click (siempre intentamos tirar y dejamos que el backend valide)
  async function onDiceClick(clickedEl) {
    if (isDiceRolling) return;
    if (!CURRENT_GAME) return;

    isDiceRolling = true;
    mostrarMensaje('Tirando dado...');
    const stop = startDiceSpin(clickedEl);

    try {
      const r = await api('roll', {});
      stop();
      if (!r?.success) {
        // Si backend responde que ya tiraste o cualquier otro error, sincronizamos
        if (r?.data?.game) {
          CURRENT_GAME = r.data.game;
          const cp = Number(CURRENT_GAME.current_player || 1);
          updateDiceUI(CURRENT_GAME);
          applyDiceLocksForPlayer(CURRENT_GAME, cp);
          updatePlayerBadge(CURRENT_GAME);
          updateRestrictionBanner(CURRENT_GAME, cp);
        } else {
          await syncStateAndRefresh();
        }
        mostrarMensaje(r?.message || 'No se pudo tirar el dado');
        return;
      }
      if (r.data?.game) CURRENT_GAME = r.data.game;

      updateDiceUI(CURRENT_GAME);
      const cp = Number(CURRENT_GAME.current_player || 1);
      applyDiceLocksForPlayer(CURRENT_GAME, cp);
      updatePlayerBadge(CURRENT_GAME);
      updateRestrictionBanner(CURRENT_GAME, cp);
      mostrarMensaje('Dado tirado. La restricción aplicará al rival en su turno.');
    } catch (e) {
      stop();
      console.error(e);
      mostrarMensaje('Error de red al tirar el dado');
      await syncStateAndRefresh();
    } finally {
      isDiceRolling = false;
    }
  }

  // Listeners
  if (btnReanudar) {
    if (!USER_ID || !gameId) {
      btnReanudar.disabled = true;
      btnReanudar.title = !USER_ID ? 'Inicia sesión para reanudar' : 'No hay partida previa para reanudar';
    }
    btnReanudar.addEventListener('click', resumeGame);
  }
  document.getElementById('btn-nueva')?.addEventListener('click', startNewGame);
  document.getElementById('dice-1')?.addEventListener('click', (e) => onDiceClick(e.currentTarget));
  document.getElementById('dice-2')?.addEventListener('click', (e) => onDiceClick(e.currentTarget));

  // No auto-reanudamos al cargar
});

/* ----------------------- UI auxiliar legado ----------------------- */
function actualizarMostrar(seccion, color) {
  const btn = document.getElementById(`btn${seccion}`);
  if (!btn) return;
  const img = document.createElement('img');
  img.src = `${IMG_BASE}minis/${dinosaurios[color]}.png`;
  img.classList.add('mini-dino');
  btn.appendChild(img);
}