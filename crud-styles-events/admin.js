
function togglePass(id) {
  const input = document.getElementById(id);
  if (input) {
    const current = input.type
    input.type = current === 'password' ? 'text' : 'password'
  }
}

// borrar espacios del input al tocar boton limpiar

let clearButton = document.getElementById('clearButton')
let inputCreate = document.querySelectorAll('.inputCreate')

clearButton.addEventListener('click', (event)=>{
    event.preventDefault()
    inputCreate.forEach((input)=>{
     input.value = ''
    })
})

// accion de ocultar y mostrar lista

function showList(a, b, c){
 if(b){
  a.style.height = '25px'
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

let iconList = document.getElementsByClassName('fa-caret-up') // Selector del icono de triangulo en las listas

let stateUser = true 
let stateProducts = true 

liUsers.addEventListener('click', ()=>{
  showList(sheetList, stateUser, iconList[0])
  stateUser = !stateUser
})

liProducts.addEventListener('click', ()=>{
  showList(sheetListProducts, stateProducts, iconList[1])
  stateProducts = !stateProducts
})
