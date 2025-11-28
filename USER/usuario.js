document.addEventListener('DOMContentLoaded', () => {
    // ------------------ BUSCADOR DE PRODUCTOS ------------------
    let btnEdit = document.getElementById('btnEdit');
    let modalWindow = document.querySelector('.modalWindow');
    let modalContainer = document.querySelector('.modalContainer');
    let buttonBack = document.querySelector('.back-icon');

    if (btnEdit) {
        btnEdit.addEventListener('click', () => {
            modalWindow.style.opacity = "1";
            modalWindow.style.pointerEvents = "auto";
            modalContainer.style.opacity = "1";
            modalContainer.style.pointerEvents = "auto";
            modalContainer.style.transform = "translateY(0)";
        });
    }

    if (buttonBack) {
        buttonBack.addEventListener('click', () => {
            modalContainer.style.transform = "translateY(-100%)";
            modalContainer.style.opacity = "0";
            modalWindow.style.opacity = "0";
            modalWindow.style.pointerEvents = "none";
        });
    }

    //Script de peticion al servidor para busqueda en el buscador

    const input_request = document.getElementById('input-search-product');
    const box_result = document.getElementById('results-container');

    input_request.addEventListener('input', async (event) => {
        const textoDelUsuario = event.target.value;
        //limpiamos los resultados anteriores
        box_result.innerHTML = '';

        if (textoDelUsuario.length < 2) { //opcional: buscar solo a partir de 2 caracteres
            box_result.style.display = 'none';
            return;
        }
        //1. HACER LA PETICION AL SERVIDOR PHP
        try {
            //hacemos la llamada al php dandole el valor por medio del ?termn=
            const respuesta_producto = await fetch(`../buscar-productos.php?term=${textoDelUsuario}`);
            //desempacamos la respuesta del server
            const productos = await respuesta_producto.json(); // .json convierte la respuesta venida del servidor(texto Plano) y lo convierte en un array de oroducto listo para utilizar
            if (productos.length > 0) {
                box_result.style.display = 'block';
                productos.forEach(producto => { //se peude utilizar otro nombre en lugar de producto, no afecta en nada ya que es un nombrte de la funcion temporal
                    const item = document.createElement('div') //esto creara varios divs que correspondan al elemento puesto en la barra de busqueda
                    item.classList.add('result-item')
                    item.textContent = producto // asignamos al div, un texto a partir del array recorrido, utilizamos para eso el nombre de la funcion productos
                    item.addEventListener('click', () => {
                        input_request.value = producto;
                        box_result.style.display = 'none';
                    });
                    //poner el div item creado en el div contenedor que se creo posteriormente
                    box_result.appendChild(item)

                });
            } else {
                box_result.style.display = 'none';
            }
        } catch (error) {
            console.error("Error al buscar productos:", error);
            box_result.style.display = 'none';

        };
    });

    // vamos a ocultar la barra si hace click fuera de la busqueda
    document.addEventListener('click', (event_close) => {
        if (!input_request.contains(event.target) && !box_result.contains(event.target)) {
            box_result.style.display = 'none'
        }
    })

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


    // enlace a otras paginas

    let linkSell = document.getElementById('venderPage')

    if (linkSell) {
        linkSell.addEventListener('click', () => {
            window.location.href = '../seller/comprobatorio.php'
        })
    }
    // Elementos del menú de categorías y ayuda
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
})