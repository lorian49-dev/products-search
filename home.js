
// Esperamos a que todo el contenido de la página (HTML) esté cargado
document.addEventListener('DOMContentLoaded', () => {
    // Elementos del menú de categorías y ayuda

    const triggerCategories = document.getElementById('span-menu-categoria')
    const divCategories = document.getElementById('menu-categoria')
    const triggerHelp = document.getElementById('ayuda-listado')
    const divHelp = document.getElementById('menu-ayuda')

    // Función para manejar eventos de apertura/cierre de menús

    function listTriggerEvents(req1, req2) {
        req1.addEventListener('click', (event) => {
            event.stopPropagation()
            const isVisible = req2.style.opacity === '1'
            req2.style.opacity = isVisible ? '0' : '1'

            if (req2.style.opacity === '1') {
                req2.style.pointerEvents = 'auto'
            } else {
                req2.style.pointerEvents = 'none'
            }
        })

        document.addEventListener('click', (event) => {
            if (req2.style.opacity === '1' && !req2.contains(event.target)) {
                req2.style.opacity = '0'
                req2.style.pointerEvents = 'none'

            }
        })
    }

    if (triggerCategories && divCategories) {
        listTriggerEvents(triggerCategories, divCategories) // aqui llamamos a nuestra funcion dandole los argumentos que necesita para funcionar
    }
    if (triggerHelp && divHelp) {
        listTriggerEvents(triggerHelp, divHelp) // igual aca, solo que este es para la lista de ayuda
    }



    /*triggerCategories.addEventListener('click', (event)=>{
        event.stopPropagation()
        const isVisible = divCategories.style.opacity === '1'
        divCategories.style.opacity = isVisible?'0':'1'
    
        if(divCategories.style.opacity === '1'){
        divCategories.style.pointerEvents = 'auto'
        } else{
            divCategories.style.pointerEvents = 'none'
        }
    })
    
    document.addEventListener('click', (event)=>{
        if(divCategories.style.opacity==='1' && !divCategories.contains(event.target)){
           divCategories.style.opacity = '0'
           divCategories.style.pointerEvents = 'none'
    
        }
    })*/


    // Evento de cursor sobre informacion del inicio del usuario y perfil

    let btnProfile = document.getElementsByClassName('perfil-btn')[0]
    let btnProfileContainer = document.getElementsByClassName('dropdown-content')[0]

    if (btnProfile && btnProfileContainer) {
        listTriggerEvents(btnProfile, btnProfileContainer)

        btnProfile.addEventListener('click', (event) => {
            event.stopPropagation()
            btnProfile.classList.toggle('active')
            // btnProfile.classList.add('active')
        })

        document.addEventListener('click', (event) => {
            if (btnProfile.classList.contains('active') && !btnProfile.contains(event.target)) {
                btnProfile.classList.remove('active')
            }
        })
    }



    // CARRUSEL----------------------------------------------------

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

        //  ¿El carrusel está visible?
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



    // enlace a otras paginas

    let linkSell = document.getElementById('venderPage')

    if (linkSell) {
        linkSell.addEventListener('click', () => {
            window.location.href = 'seller/comprobatorio.php'
        })
    }

    document.addEventListener('DOMContentLoaded', () => {
        // ------------------ BUSCADOR DE PRODUCTOS ------------------

        const inputSearch = document.getElementById("buscador");
        const boxSearch = document.getElementById("resultados-busqueda");

        if (inputSearch && boxSearch) {

            inputSearch.addEventListener("input", function () {
                let term = this.value.trim();

                if (term.length === 0) {
                    boxSearch.style.display = "none";
                    return;
                }

                fetch(`buscar-productos.php?q=${textoDelUsuario}`)
                    .then(res => res.json())
                    .then(data => {
                        boxSearch.innerHTML = "";

                        if (data.length === 0) {
                            boxSearch.style.display = "none";
                            return;
                        }

                        data.forEach(nombre => {
                            let item = document.createElement("div");
                            item.textContent = nombre;

                            item.onclick = () => {
                                inputSearch.value = nombre;
                                boxSearch.style.display = "none";
                            };

                            boxSearch.appendChild(item);
                        });

                        boxSearch.style.display = "block";
                    });
            });

            // Cerrar la lista si hago clic afuera
            document.addEventListener("click", (e) => {
                if (!inputSearch.contains(e.target)) {
                    boxSearch.style.display = "none";
                }
            });

        }

    });

    // Imagenes de prueba para el carrusel de productos  

    const preview_image = document.querySelector('.preview-image')

    preview_image.style.backgroundImage = 'url("https://www.laces.mx/cdn/shop/files/DD8959-115_1.jpg?v=1754878157&width=1024")';

    const arrayColors = ['#F8FAFC', '#F4320B']

    let preview_color = document.querySelector('.preview-color')
})