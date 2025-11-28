let logoPage = document.getElementById('logo-hermes-home')
let logoHomepage = document.getElementsByClassName('fa-home')[0]

function goHome(){
 window.location.href = "../home.php"
}

logoPage.addEventListener('click', goHome)
logoHomepage.addEventListener('click', goHome)


ScrollReveal().reveal('.scrolling', {
    delay: 50,
    duration: 1500,
    reset: false
});

// Efecto transicion al cargar la pagina en el contenido del slogan

let slogan = document.querySelector('.slogan');
let incentives = document.querySelector('.incentives')

document.addEventListener('DOMContentLoaded', ()=>{
    setTimeout(()=>{
     slogan.style.transform = 'translateX(0)'
    slogan.style.opacity = '1'
    incentives.style.transform = 'translateX(0)'
    incentives.style.opacity = '1'
    }, 1000)
})