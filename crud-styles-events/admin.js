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

clearButton.addEventListener('click', (event)=>{
    event.preventDefault()
    inputCreate.forEach((input)=>{
     input.value = ''
    })
})

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

liUsers.addEventListener('click', ()=>{
  showList(sheetList, stateUser, iconList[0])
  stateUser = !stateUser
})

liProducts.addEventListener('click', ()=>{
  showList(sheetListProducts, stateProducts, iconList[1])
  stateProducts = !stateProducts
})

liGets.addEventListener('click', ()=>{
  showList(sheetListGets, stateGets, iconList[2])
  stateGets = !stateGets
})

liStats.addEventListener('click', ()=>{
  showList(sheetListStats, stateStats, iconList[3])
  stateStats = !stateStats
})

liAbout.addEventListener('click', ()=>{
  showList(sheetListAbout, stateAbout, iconList[4])
  stateAbout = !stateAbout
})

// Aqui termina la accion de mostrar la lista de la barra de navegacion--------------------------------------------------------------------------------------------


let btnEdit = document.querySelectorAll('.btn-edit')
let modalWindow = document.getElementsByClassName('modalWindow')[0]
let modalContainer = document.getElementsByClassName('modalContainer')[0]
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

const buttonBack = document.querySelector('.back-icon')

buttonBack.addEventListener('click', ()=>{
    modalWindow.style.opacity = '0'
    modalWindow.style.pointerEvents = 'none'
    modalContainer.style.opacity = '0'
    modalContainer.style.pointerEvents = 'none'
    modalContainer.style.transform = 'translateY(-100%)'
})