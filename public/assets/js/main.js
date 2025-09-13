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
let campoColores = { 1: [], 2: [], 3: [], 4: [], 5: [], 6: [], 7: [] };

let [scoreUno, scoreDos, scoreTres, scoreCuatro, scoreCinco, scoreSeis, scoreSiete] = [0, 0, 0, 0, 0, 0];

/* ============================================================
   Bases dinámicas (evita rutas rotas)
   - IMG_BASE: resuelve a /public/assets/imgs/ en cualquier entorno
   - API_URL: usa la URL actual (?page=jugar) para llamar al backend
   ============================================================ */
const IMG_BASE = (() => {
  try {
    // main.js está en /assets/js/main.js -> ../imgs/ => /assets/imgs/
    return new URL('../imgs/', document.currentScript.src).href;
  } catch {
    // Fallback por si currentScript no existe
    return '/assets/imgs/';
  }
})();

// Apunta a la URL actual (por ejemplo /.../public/?page=jugar)
const API_URL = new URL(window.location.href);

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

function slctRojo() { seleccion = 0; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-rojo'); if (el) el.classList.add('cambio'); }
function slctCyan() { seleccion = 1; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-cyan'); if (el) el.classList.add('cambio'); }
function slctNaranja() { seleccion = 2; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-naranja'); if (el) el.classList.add('cambio'); }
function slctRosa() { seleccion = 3; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-rosa'); if (el) el.classList.add('cambio'); }
function slctVerde() { seleccion = 4; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-verde'); if (el) el.classList.add('cambio'); }
function slctAzul() { seleccion = 5; document.querySelectorAll('.icono').forEach(i=>i.classList.remove('cambio')); const el = document.getElementById('ico-azul'); if (el) el.classList.add('cambio'); }

function actualizarMostrar(seccion, color) {
  const btn = document.getElementById(`btn${seccion}`);
  if (!btn) return;
  const img = document.createElement('img');
  img.src = `${IMG_BASE}minis/${dinosaurios[color]}.png`;
  img.classList.add('mini-dino');
  btn.appendChild(img);
}

document.addEventListener('DOMContentLoaded', () => {
  let currentPlayer = 1; // empezamos por el jugador 1
  let isSwitching = false;

  function showPlayer(p) {
    document.querySelectorAll('.contenedor-juego').forEach(el => {
      const isCurrent = Number(el.dataset.player) === p;
      el.classList.toggle('hidden', !isCurrent);
    });
    currentPlayer = p;
  }

  // Cambiar de jugador con una animación simple (slide + fade)
  function switchPlayerAnimated(nextPlayer) {
    const currentEl = document.querySelector(`.contenedor-juego[data-player="${currentPlayer}"]`);
    const nextEl = document.querySelector(`.contenedor-juego[data-player="${nextPlayer}"]`);
    if (!currentEl || !nextEl) { showPlayer(nextPlayer); return; }

    // animar salida del actual
    currentEl.classList.add('anim-out');
    setTimeout(() => {
      currentEl.classList.remove('anim-out');
      currentEl.classList.add('hidden');

      // preparar y animar entrada del siguiente
      nextEl.classList.add('anim-in');
      nextEl.classList.remove('hidden');
      void nextEl.offsetWidth; // forzar reflow para que transicione
      nextEl.classList.remove('anim-in');

      currentPlayer = nextPlayer;
    }, 320); // debe coincidir con el CSS (~320ms)
  }

  async function api(action, params = {}) {
    const isGet = action === 'init' || action === 'get_hand' || action === 'state';
    if (isGet) {
      const url = new URL(API_URL); // clonar URL actual (?page=jugar)
      url.searchParams.set('action', action);
      Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
      const res = await fetch(url.toString(), {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'include',
        cache: 'no-store',
      });
      return res.json();
    } else {
      const body = new URLSearchParams({ action, ...params });
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

  let nextDragId = 1;
  function makeDraggable(img) {
    img.setAttribute('draggable', 'true');
    if (!img.dataset.dragId) img.dataset.dragId = 'dino-' + (nextDragId++);
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
    const cont = document.querySelector(`.contenedor-juego[data-player="${player}"] .bandeja .dinosaurios`);
    if (!cont) return;
    cont.innerHTML = '';
    (hand || []).forEach(d => {
      const img = document.createElement('img');
      img.src = d.imagen;
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
      el.dataset.zoneId = z.id;
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

        const zoneId = el.dataset.zoneId;
        let freeSlot;
        if (zoneId === '2') {
          freeSlot = Array.from(el.querySelectorAll('.slot')).reverse().find(s => !s.classList.contains('filled'));
        } else {
          freeSlot = el.querySelector('.slot:not(.filled)');
        }
        if (!freeSlot) return;

        try {
          const r = await api('place', { player: currentPlayer, dino_id: dinoId });
          if (!r?.success) {
            alert(r?.message || 'Error al colocar el dinosaurio');
            return;
          }

          const placed = r.data?.placed_dino;
          if (placed) {
            const img = document.createElement('img');
            img.src = placed.imagen;
            img.alt = placed.tipo || 'dino';
            img.className = 'dino';
            img.draggable = false;

            freeSlot.classList.add('filled');
            freeSlot.appendChild(img);
          }

          renderBandejaFor(currentPlayer, r.data?.new_hand || []);

          const nextPlayer = (r?.data?.game?.current_player != null)
            ? Number(r.data.game.current_player)
            : (currentPlayer === 1 ? 2 : 1);

          try {
            const handNext = await api('get_hand', { player: nextPlayer });
            if (handNext?.success) renderBandejaFor(nextPlayer, handNext.data?.hand || []);
          } catch {}

          isSwitching = true;
          setTimeout(() => {
            switchPlayerAnimated(nextPlayer);
            isSwitching = false;
          }, 400); 

          if (r.data?.finished) alert('Partida finalizada');
        } catch (err) {
          console.error(err);
          alert('Error de red al colocar el dinosaurio');
        }
      });

      tablero.appendChild(el);
    });
  }

  document.querySelectorAll('.contenedor-juego').forEach(boardEl => {
    const player = Number(boardEl.dataset.player || '1');
    const tablero = boardEl.querySelector('.tablero');
    renderZonasFor(tablero, player);
  });

  const btn = document.getElementById('btn-iniciar');
  const pantalla = document.getElementById('pantalla-inicio');
  const err = document.getElementById('init-error');

  if (btn) {
    btn.addEventListener('click', async () => {
      if (err) err.classList.add('hidden');
      btn.disabled = true;
      btn.textContent = 'Iniciando...';

      try {
        const initRes = await api('init');
        if (!initRes?.success) throw new Error(initRes?.message || 'Error al iniciar');

        if (pantalla) pantalla.classList.add('hidden');
        showPlayer(1);

        for (const p of [1, 2]) {
          const handRes = await api('get_hand', { player: p });
          if (!handRes?.success) throw new Error(handRes?.message || `Error al obtener mano del jugador ${p}`);
          renderBandejaFor(p, handRes.data?.hand || []);
        }
      } catch (e) {
        console.error(e);
        if (err) err.classList.remove('hidden');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Jugar';
      }
    });
  }
});