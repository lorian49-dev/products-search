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


