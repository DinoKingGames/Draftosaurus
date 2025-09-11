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
  cont.textContent = texto;
}

function selectUno() {
    if (seleccion === null) {
        mostrarMensaje('Selecciona un dinosaurio');
        return;
    }
    if (campoUnoColor === undefined) {
        campoUnoColor = seleccion;
    }
    const puntos = [0,2,4,8,12,18,24];
    if (seleccion !== campoUnoColor) {
        mostrarMensaje('No se acepta');
        return;
    }
    if (campoUnoCantidad >= 6) {
        mostrarMensaje('Máximo 6 dinosaurios');
        return;
    }
    campoUnoCantidad++;
    campoColores[1].push(seleccion);
    totalPorColor[seleccion]++;
    scoreUno = puntos[campoUnoCantidad];
    totalColocados++;
    actualizarMostrar(1, seleccion);
    terminarPartida();
}

function selectDos() {
    if (seleccion === null) {
        mostrarMensaje('Selecciona un dinosaurio');
        return;
    }
    campoDosCantidad++;
    campoColores[2].push(seleccion);
    totalPorColor[seleccion]++;
    scoreDos = campoDosCantidad;
    totalColocados++;
    actualizarMostrar(2, seleccion);
    terminarPartida();
}

function selectTres() {
    if (seleccion === null) {
        mostrarMensaje('Selecciona un dinosaurio');
        return;
    }
    if (campoTresCantidad >= 1) {
        mostrarMensaje('Solo se permite ingresar un dinosaurio');
        return;
    }
    campoTresColor = seleccion;
    campoTresCantidad++;
    campoColores[3].push(seleccion);
    totalPorColor[seleccion]++;
    let max = 0;
    for (let i = 0; i < totalPorColor.length; i++) {
        if (totalPorColor[i] > max) max = totalPorColor[i];
    }
    scoreTres = (totalPorColor[campoTresColor] === max) ? 7 : 0;
    totalColocados++;
    actualizarMostrar(3, seleccion);
    terminarPartida();
}

function selectCuatro() {
    if (seleccion === null) {
        mostrarMensaje('Selecciona un dinosaurio');
        return;
    }
    campoCuatroCantidad++;
    campoColores[4].push(seleccion);
    totalPorColor[seleccion]++;
    scoreCuatro = (campoCuatroCantidad === 3) ? 7 : 0;
    totalColocados++;
    actualizarMostrar(4, seleccion);
    terminarPartida();
}

function selectCinco() {
    if (seleccion === null) {
        mostrarMensaje('Selecciona un dinosaurio');
        return;
    }
    if (campoCincoUsados[seleccion]) {
        mostrarMensaje('No puedes tener 2 dinosaurios del mismo color');
        return;
    }
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
    if (seleccion === null) {
        mostrarMensaje('Selecciona un dinosaurio');
        return;
    }
    campoSeisCantidad++;
    campoColores[6].push(seleccion);
    totalPorColor[seleccion]++;
    let pares = 0;
    for (let i = 0; i < totalPorColor.length; i++) {
        pares += Math.floor(totalPorColor[i]/2);
    }
    scoreSeis = pares * 5;
    totalColocados++;
    actualizarMostrar(6, seleccion);
    terminarPartida();
}

function selectSiete() {
    if (seleccion === null) {
        mostrarMensaje('Selecciona un dinosaurio');
        return;
    }
    if (campoSieteCantidad >= 1) {
        mostrarMensaje('Solo se permite un dinosaurio aquí');
        return;
    }
    campoSieteCantidad++;
    campoColores[7].push(seleccion);
    totalPorColor[seleccion]++;
    scoreSiete = 7;
    totalColocados++;
    actualizarMostrar(7, seleccion);
    terminarPartida();
}

function slctRojo() {
    seleccion = 0;
    document.querySelectorAll('.icono').forEach(icon => icon.classList.remove('cambio'));
    document.getElementById('ico-rojo').classList.add('cambio');
}

function slctCyan() {
    seleccion = 1;
    document.querySelectorAll('.icono').forEach(icon => icon.classList.remove('cambio'));
    document.getElementById('ico-cyan').classList.add('cambio');
}

function slctNaranja() {
    seleccion = 2;
    document.querySelectorAll('.icono').forEach(icon => icon.classList.remove('cambio'));
    document.getElementById('ico-naranja').classList.add('cambio');
}

function slctRosa() {
    seleccion = 3;
    document.querySelectorAll('.icono').forEach(icon => icon.classList.remove('cambio'));
    document.getElementById('ico-rosa').classList.add('cambio');
}

function slctVerde() {
    seleccion = 4;
    document.querySelectorAll('.icono').forEach(icon => icon.classList.remove('cambio'));
    document.getElementById('ico-verde').classList.add('cambio');
}

function slctAzul() {
    seleccion = 5;
    document.querySelectorAll('.icono').forEach(icon => icon.classList.remove('cambio'));
    document.getElementById('ico-azul').classList.add('cambio');
}

function actualizarMostrar(seccion, color) {
  const btn = document.getElementById(`btn${seccion}`);
  const img = document.createElement('img');
  img.src = `imgs/minis/${dinosaurios[color]}.png`;
  img.classList.add('mini-dino');
  btn.appendChild(img);
}

/* Juego */

document.addEventListener('DOMContentLoaded', () => {
  const tablero = document.getElementById('tablero');
  if (!tablero) throw new Error('No se encontró #tablero en el DOM');
  let nextDragId = 1;
  function makeDraggable(img) {
    img.setAttribute('draggable', 'true');
    if (!img.dataset.dragId) img.dataset.dragId = 'dino-' + (nextDragId++);
    img.addEventListener('dragstart', (e) => {
      const payload = JSON.stringify({
        src: e.currentTarget.src,
        id: e.currentTarget.dataset.dragId,
        tipo: e.currentTarget.dataset?.tipo || null,
      });
      e.dataTransfer.setData('text/plain', payload);
      e.dataTransfer.effectAllowed = 'copyMove';
    });
  }
  document.querySelectorAll('.mini-dino').forEach(makeDraggable);

  const ZONAS = [
    { id: 1, nombre: 'Bosque Semejanza', x: 0,  y: 2,  w: 38, h: 32, slots: 6, cols: 6 },
    { id: 3, nombre: 'Trío Frondoso',    x: 10, y: 37, w: 23, h: 18, slots: 3, cols: 3 },
    { id: 6, nombre: 'Pradera del Amor', x: 4,  y: 72, w: 18, h: 22, slots: 6, cols: 6 }, 
    { id: 4, nombre: 'Rey de la Selva',  x: 69, y: 9,  w: 15, h: 18, slots: 1, cols: 1 },
    { id: 5, nombre: 'Prado Diferencia', x: 69, y: 46, w: 25, h: 25, slots: 6, cols: 6 },
    { id: 7, nombre: 'Isla Solitaria',   x: 68, y: 78, w: 20, h: 22, slots: 1, cols: 1 },
    { id: 2, nombre: 'Río',              x: 50, y: 0,  w: 8,  h: 100, slots: 8, cols: 1 }, 
  ];

  function renderZonas() {
    ZONAS.forEach(z => {
      const el = document.createElement('div');
      el.className = 'dropzone';
      el.style.left = `${z.x}%`;
      el.style.top  = `${z.y}%`;
      el.style.width  = `${z.w}%`;
      el.style.height = `${z.h}%`;
      el.dataset.zoneId = z.id;

      const label = document.createElement('span');
      label.className = 'label';
      label.textContent = z.nombre ?? ('Zona ' + z.id);
      el.appendChild(label);
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
        el.classList.add('is-over');
        if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';
      });
      el.addEventListener('dragleave', () => el.classList.remove('is-over'));

      el.addEventListener('drop', (e) => {
        e.preventDefault();
        el.classList.remove('is-over');

        const raw = e.dataTransfer.getData('text/plain');
        if (!raw) return;

        let data;
        try { data = JSON.parse(raw); } catch { data = { src: raw, id: null }; }

        const zoneId = el.dataset.zoneId;
        let freeSlot;

        if (zoneId === '2') {
          freeSlot = Array.from(el.querySelectorAll('.slot')).reverse()
            .find(s => !s.classList.contains('filled'));
        } else {
          freeSlot = el.querySelector('.slot:not(.filled)');
        }

        if (!freeSlot) {
          return;
        }

        const img = document.createElement('img');
        img.src = data.src;
        img.alt = data.tipo || 'dino';
        img.className = 'dino';
        img.draggable = false;

        freeSlot.classList.add('filled');
        freeSlot.appendChild(img);
      });

      tablero.appendChild(el);
    });
  }

  renderZonas();
});