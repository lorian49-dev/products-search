const menuTrigger = document.getElementById('span-menu-categoria');
const menuTrigger_help = document.getElementById('ayuda-listado')
const menuDiv = document.getElementById('menu-categoria');
const menuDiv_help = document.getElementById('menu-ayuda');

menuTrigger.addEventListener('click', (event) => {
    // Evita que al hacer clic en el trigger se cierre el menú inmediatamente
    event.stopPropagation(); 
    
    // Muestra u oculta el menú cada vez que se hace clic
    const isVisible = menuDiv.style.display === 'block';
    menuDiv.style.display = isVisible ? 'none' : 'block';
});

menuTrigger_help.addEventListener('click', (event) => {
    // Evita que al hacer clic en el trigger se cierre el menú inmediatamente
    event.stopPropagation(); 
    
    // Muestra u oculta el menú cada vez que se hace clic
    const isVisible = menuDiv_help.style.display === 'block';
    menuDiv_help.style.display = isVisible ? 'none' : 'block';
});

// Opcional pero muy recomendado: Cierra el menú si se hace clic en cualquier otro lugar de la página
document.addEventListener('click', (event) => {
    // Si el menú está visible Y el clic fue fuera del menú
    if (menuDiv.style.display === 'block' && !menuDiv.contains(event.target)) {
        menuDiv.style.display = 'none';
    }

    if (menuDiv_help.style.display === 'block' && !menuDiv_help.contains(event.target) ) {
        menuDiv_help.style.display = 'none';
    }
});

// CARRUSEL----------------------------------------------------

// Esperamos a que todo el contenido de la página (HTML) esté cargado
document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Seleccionamos los elementos y preparamos las variables ---
    const contenedorCarrusel = document.querySelector('.header-box');
    const carrusel = document.querySelector('.slides');
    const totalSlides = document.querySelectorAll('.slides-box').length;

    let currentIndex = 0; // Para saber en qué slide estamos
    let intervalID;     // Para guardar el "ticket" de nuestro intervalo (inicia vacío)

    // --- 2. La función que mueve el carrusel (nuestro motor) ---
    function moverSlides() {
        currentIndex = (currentIndex + 1) % totalSlides;
        const offset = -currentIndex * 100;
        carrusel.style.transform = `translateX(${offset}%)`;
    }

    // --- 3. El vigilante (IntersectionObserver) ---
    const observer = new IntersectionObserver((entries) => {
        // 'entries' es el informe del vigilante
        const entry = entries[0];
        
        // La gran decisión: ¿El carrusel está visible?
        if (entry.isIntersecting) {
            // Si está visible, le damos a "play"
            // Guardamos el ID que nos devuelve setInterval
            intervalID = setInterval(moverSlides, 3000); // Cambia cada 3 segundos
        } else {
            // Si no está visible, le damos a "stop"
            // Usamos el ID que guardamos para detener el intervalo correcto
            clearInterval(intervalID);
        }
    });

    // --- 4. Le decimos al vigilante qué elemento debe observar ---
    observer.observe(contenedorCarrusel);

});

// enlace a otras paginas

let linkSell = document.getElementById('venderPage')

linkSell.addEventListener('click', ()=>{
    window.location.href = 'seller/dashboardSeller.php'
})