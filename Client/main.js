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
        id: e.currentTarget.dataset.dragId
      });
      e.dataTransfer.setData('text/plain', payload);
      e.dataTransfer.effectAllowed = 'copyMove';
    });
  }


  document.querySelectorAll('.mini-dino').forEach(makeDraggable);

  tablero.addEventListener('dragover', (e) => {
    e.preventDefault();
    if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';
  });

    tablero.addEventListener('drop', (e) => {
    e.preventDefault();

    const raw = e.dataTransfer.getData('text/plain');
    if (!raw) return; 

    let data;
    try {
      data = JSON.parse(raw);
    } catch {
      data = { src: raw, id: null };
    }

    const rect = tablero.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const clon = document.createElement('img');
    clon.src = data.src;
    clon.classList.add('dino');
    clon.style.position = 'absolute';
    clon.style.width = '60px';
    clon.style.height = '60px';
    clon.style.left = `${x - 30}px`;
    clon.style.top = `${y - 30}px`;

    clon.setAttribute('draggable', 'false');

    tablero.appendChild(clon);
});
});