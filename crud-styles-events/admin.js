
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

// accion al icono de home

 let inicio = document.getElementById('icon-home')

 if(inicio){
   console.log('Hola')
 }