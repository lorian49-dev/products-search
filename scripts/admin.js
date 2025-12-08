// Carga suave de contenido

window.addEventListener('load', ()=>{setTimeout(()=>{document.body.classList.add('active')},500)})

// Este bloque de codigo es para mostrar y ocultar la contrasena del usuario en cuestion ------------------------------------------------------------------------------------
function togglePass(id) {
  const input = document.getElementById(id);
  if (input) {
    const current = input.type
    input.type = current === 'password' ? 'text' : 'password'
  }
}

// Evento del boton Limpiar, aqui limpia los datos del input ----------------------------------------------------------------------------------------------------------------

let clearButton = document.getElementById('clearButton')
let inputCreate = document.querySelectorAll('.inputCreate')

if(clearButton){
clearButton.addEventListener('click', (event)=>{
    event.preventDefault()
    inputCreate.forEach((input)=>{
     input.value = ''
    })
})
}

// accion de ocultar y mostrar lista ----------------------------------------------------------------------------------------------------------------------------------------

function showList(a, b, c){
 if(b){
  a.style.height = '90px'
  c.style.transform = 'rotate(180deg)'
  return 
 } else{
  a.style.height = '0px'
  c.style.transform = 'rotate(0deg)'
  return
 }
}

let liUsers = document.getElementById('liUsers')
let sheetList = document.querySelector('.sheetList')
let liProducts = document.getElementById('liProducts')
let sheetListProducts = document.querySelector('.sheetListProducts')
let liGets = document.getElementById('liGets')
let sheetListGets = document.querySelector('.sheetListGets')
let liStats = document.getElementById('liStats')
let sheetListStats = document.querySelector('.sheetListStats')
let liAbout = document.getElementById('liAbout')
let sheetListAbout = document.querySelector('.sheetListAbout')

let iconList = document.getElementsByClassName('fa-caret-up') // Selector del icono de triangulo en las listas

let stateUser = true 
let stateProducts = true 
let stateGets= true 
let stateStats=true
let stateAbout=true

if (liUsers) {
  liUsers.addEventListener('click', () => {
    showList(sheetList, stateUser, iconList[0])
    stateUser = !stateUser
  })
}

if (liProducts) {
  liProducts.addEventListener('click', () => {
    showList(sheetListProducts, stateProducts, iconList[1])
    stateProducts = !stateProducts
  })
}

if (liGets) {
  liGets.addEventListener('click', () => {
    showList(sheetListGets, stateGets, iconList[2])
    stateGets = !stateGets
  })
}

if (liStats) {
  liStats.addEventListener('click', () => {
    showList(sheetListStats, stateStats, iconList[3])
    stateStats = !stateStats
  })
}

if (liAbout) {
  liAbout.addEventListener('click', () => {
    showList(sheetListAbout, stateAbout, iconList[4])
    stateAbout = !stateAbout
  })
}


// Aqui termina la accion de mostrar la lista de la barra de navegacion--------------------------------------------------------------------------------------------


let btnEdit = document.querySelectorAll('.btn-edit')
let modalWindow = document.getElementsByClassName('modalWindow')[0]
let modalContainer = document.getElementsByClassName('modalContainer')[0]

if(btnEdit){
btnEdit.forEach(promise =>{
  promise.addEventListener('click', (event)=>{
    event.preventDefault()

    // obtenemos los valores en las variables

    const id = promise.dataset.id
    const name = promise.dataset.name
    const lastname = promise.dataset.lastname
    const email = promise.dataset.email
    const password = promise.dataset.password
    const birthday = promise.dataset.birthday
    const phone = promise.dataset.phone

    const form = document.querySelector('.formUpdateUser')

    form.id_usuario.value = id
    form.name.value = name
    form.lastname.value = lastname
    form.email.value = email
    form.password.value = password
    form.birthday.value = birthday
    form.phone.value = phone

    modalWindow.style.opacity = '1'
    modalWindow.style.pointerEvents = 'auto'

    setTimeout(()=>{
    modalContainer.style.opacity = '1'
    modalContainer.style.pointerEvents = 'auto'
    modalContainer.style.transform = 'translateY(0)'
    }, 500)
    
  })
})
}

const buttonBack = document.querySelector('.back-icon')

if(buttonBack){
  buttonBack.addEventListener('click', ()=>{
    modalWindow.style.opacity = '0'
    modalWindow.style.pointerEvents = 'none'
    modalContainer.style.opacity = '0'
    modalContainer.style.pointerEvents = 'none'
    modalContainer.style.transform = 'translateY(-100%)'
})
}

// Eventos para el cambio de color o modo de color visual Dark o light


const btnLight = document.querySelector('.light-mode');
const btnDark = document.querySelector('.dark-mode');
const background = document.querySelector('.background-modes');

const body = document.querySelector('body')
const navegation_bar = document.getElementById('navegation')
const text_h2 = document.querySelectorAll('h2')
const buttonsToColorModes = document.querySelectorAll('.toChangeColor')
const table = document.querySelectorAll('table')

const advise = document.querySelector('.advertencia')

// Define la propiedad de transición que deseas aplicar con los clics
const TRANSITION_STYLE = 'all 1s ease'; 

// 1. Colección de todos los elementos que cambian de estilo y necesitan transición
const transitionableElements = [
    body, 
    navegation_bar, 
    background, 
    btnLight, 
    btnDark, 
    // Convertir y añadir los NodeLists a la colección:
    ...(text_h2 ? Array.from(text_h2) : []),
    ...(buttonsToColorModes ? Array.from(buttonsToColorModes) : []),
    ...(table ? Array.from(table) : []) 
].filter(Boolean); // Filtra cualquier elemento que no se encuentre

let isDarkMode = false; // estado inicial

/**
 * Habilita la animación de transición en todos los elementos relevantes.
 */
function enableTransitions() {
    transitionableElements.forEach(element => {
        // Establece la transición para que SÍ se active con los clics posteriores
        element.style.transition = TRANSITION_STYLE;
    });
}

/**
 Aplica los estilos de modo claro u oscuro y guarda la preferencia.
 */
function setMode(dark) {
    if (dark) {
        // --- Lógica para Modo Oscuro ---
        background.classList.add('dark-mode-active');
        background.classList.remove('light-mode-active');
        btnLight.style.color = '#fff8f1';
        btnDark.style.color = '#131313ff';
        body.style.backgroundColor = '#2f2f2fff'
        navegation_bar.style.backgroundColor = '#131313ff'
        text_h2.forEach(h2 => h2.style.color = '#fff8f1') 
        if(buttonsToColorModes){buttonsToColorModes.forEach(button => button.style.backgroundColor = '#131313ff') }
        if(table){table.forEach(tb => tb.classList.add('table-dark'))}
        if(advise){advise.classList.add('dark-mode-active-advise')}
    } else {
        // --- Lógica para Modo Claro ---
        background.classList.add('light-mode-active');
        background.classList.remove('dark-mode-active');
        btnLight.style.color = '#461d01';
        btnDark.style.color = '#fff8f1';
        body.style.backgroundColor = '#fff8f1'
        navegation_bar.style.backgroundColor = '#461d01'
        text_h2.forEach(h2 => h2.style.color = '#131313ff')
        if(buttonsToColorModes){buttonsToColorModes.forEach(button => button.style.backgroundColor = '#461d01') }
        if(table){table.forEach(tb => tb.classList.remove('table-dark'))}
        if(advise){advise.classList.remove('dark-mode-active-advise')}
    }
    
    // 💾 GUARDAR la preferencia en Local Storage
    localStorage.setItem('darkMode', dark);
    
    isDarkMode = dark;
}

/**
 * Lee la preferencia guardada al cargar la página y la aplica SIN animación.
 */
function initializeMode() {
    // 1. Deshabilitar la transición para que la carga sea instantánea
    transitionableElements.forEach(element => {
        element.style.transition = 'none';
    });
    
    // 2. Aplicar el modo guardado (instantáneamente)
    const savedMode = localStorage.getItem('darkMode');

    if (savedMode !== null) {
        const shouldBeDark = savedMode === 'true'; 
        // Llama a setMode, que aplica los estilos sin animación
        setMode(shouldBeDark); 
    } else {
        // Si no hay preferencia, puedes establecer el modo por defecto (ej: claro)
        // setMode(false);
    }

    // 3. ⚠️ Reactivar la transición después de un pequeño retraso (50ms)
    // Esto es crucial para que la transición solo se aplique en clics futuros.
    setTimeout(enableTransitions, 50);
}

// 🚀 Inicializar el modo y controlar la transición al cargar la página
initializeMode();


// --- Event Listeners para el clic de usuario ---
if (btnDark && btnLight) {
    btnDark.addEventListener('click', () => setMode(true));
    btnLight.addEventListener('click', () => setMode(false));
}