let logoPage = document.getElementById('logo-hermes-home')

logoPage.addEventListener('click', ()=>{
    window.location.href = "../home.php"
})

ScrollReveal().reveal('.scrolling', {
    delay: 50,
    duration: 1500,
    reset: false
});