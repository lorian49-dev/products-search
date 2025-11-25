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