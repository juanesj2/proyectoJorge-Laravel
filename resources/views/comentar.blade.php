<!-- Esta es la vista para ver los comentarios de la imagen -->
@extends('master')

@section('contenido')
<!-- Contenedor principal de todo -->
<div class="container mt-5 d-flex justify-content-center">
    
    <!-- Contenedor para la fotografía y los comentarios -->
    <!-- flex-lg-row: Muestra la imagen y los comentarios en fila en pantallas grandes -->
    <!-- flex-column: Asegura que en pantallas pequeñas los comentarios vayan debajo -->
    <div class="col-md-8 d-flex flex-column flex-lg-row align-items-start">
        
        <!-- Card de la fotografía -->
        <div class="card mb-4 w-100">
            
            <!-- Cabecera de la imagen -->
            <div class="card-header">
                <span>Publicación de: <strong>{{ $fotografia->user->name }}</strong></span>
            </div>

            <!-- Imagen publicada -->
            <div>
                <img id="laimagen" src="{{ asset('images/' . $fotografia->direccion_imagen) }}" class="img-fluid w-100" alt="La imagen del usuario">
            </div>

            <!-- Contenedor para la información de la imagen -->
            <div class="card-body p-3 d-flex justify-content-between">

                <!-- Contenedor de los likes (izquierda) -->
                <div>
                    <!-- Comprobamos si el usuario ha dado o no like -->
                    <button type="button" role="button" aria-label="Me gusta esta foto" class="btn p-0" onclick="{{ $fotografia->likes()->where('usuario_id', Auth::id())->exists() ? 'quitarLike(this)' : 'darLike(this)' }}" fotoId="{{ $fotografia->id }}">
                        <i class="fa-solid fa-heart fs-4" id="corazon-{{ $fotografia->id }}" style="{{ $fotografia->likes()->where('usuario_id', Auth::id())->exists() ? 'color: red;' : '' }}"></i>
                    </button>
                    <!-- Contador de likes -->
                    <span id="contadorLikes-{{ $fotografia->id }}">{{ $fotografia->likesCount() }}</span>
                </div>

                <!-- Botón de comentarios (derecha) -->
                <div>
                    <!-- Comprobamos si el usuario ha hecho un comentario o no -->
                    <button type="submit" class="btn p-0" style="cursor: default;" role="button" aria-label="Comentar en esta foto">
                        <i class="fa-solid fa-comment fs-4" style="{{ \App\Models\Comentarios::comprobarComentario($fotografia->id) ? 'color: #FFD700;' : '' }}"></i>
                    </button>
                    <!-- Contador de comentarios -->
                    <span id="contadorComentarios-{{ $fotografia->id }}">{{ $fotografia->comentariosCount() }}</span>
                </div>
            </div>

            <!-- Descripción y título debajo -->
            <p class="ms-2"><strong>{{ $fotografia->user->name }}</strong> {{ $fotografia->titulo }}</p>
            <p class="text-muted ms-2">{{ $fotografia->descripcion }}</p>

        </div>

        <!-- Card de los comentarios -->
        <!-- ms-lg-4: Da un margen a la izquierda solo en pantallas grandes para separar imagen y comentarios -->
        <div class="ms-lg-4 w-100">
            <div class="card mb-4 p-3 h-100">
                
                <!-- Contenedor donde se cargan los comentarios -->
                <div id="comentarios" class="mb-3" style="max-height: 390px; overflow-y: auto;">
                    <!-- Aquí se cargan los comentarios dinámicamente -->
                </div>

                <!-- Formulario para crear un nuevo comentario -->
                <form action="{{ route('comentarios.store') }}" method="POST" class="mt-auto d-flex flex-column">
                    @csrf  <!-- Token CSRF obligatorio en Laravel -->
                    <input type="hidden" name="fotografia_id" value="{{ $fotografia->id }}">
                    
                    <div class="form-group">
                        <textarea name="comentario" id="comentario" class="form-control" rows="4" placeholder="Añade un comentario..." required></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <!-- Botón de Enviar Comentario -->
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane"></i> Enviar Comentario
                        </button>

                        <!-- Botón de Volver -->
                        <a href="{{ url('/fotografias') }}">
                            <button type="button" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Volver
                            </button>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

    //**************************************************************/
    //**************************************************************/
    //                        Comentarios
    //**************************************************************/
    //**************************************************************/

    // Con esta funcion cargaremos los comentarios 
    function cargarComentarios() {
        const fotoId = '{{ $fotografia->id }}'; // ID de la fotografia
        const userId = '{{ Auth::id() }}';  // ID del usuario logueado

        // Usamos JQuery para hacer una solicitud GET la URL es "/fotografias/" + fotoId + "/comentarios"
        $.get("/fotografias/" + fotoId + "/comentarios", function(comentarios) {

            // En esta variable es donde almacenamos los comentarios de la foto
            let comentariosHtml = '';

            // Recorremos los comentarios y los vamos metiendo en su contenedor
            comentarios.forEach(function(comentario) {
                comentariosHtml += `
                    <div class="mb-2 d-flex justify-content-between">
                        <!-- Aqui aparecen los datos de cada comentario -->
                        <div>
                            <strong>${comentario.user.name}</strong>
                            <p>${comentario.contenido}</p>
                            <small class="text-muted">${comentario.fecha}</small>
                        </div>

                        <!-- Comprobamos si el usuario logeado es el dueño del comentario para poner el boton para eliminarlo -->
                        ${comentario.user.id == userId ? 
                        `
                        <button class="btn" onclick="eliminarComentario(${comentario.id})" role="button" aria-label="Eliminar este comentario">
                        <i class="fa-solid fa-trash"></i>
                        </button>
                        ` : ''}
                    </div>
                `;
            });

            // Ahora que ya tenemos los comentarios cargados reemplazamos el contenido de #comentarios por comentariosHtml
            $('#comentarios').html(comentariosHtml);
        }).fail(function() { // Si por cualquier cosa da un error al cargar los comentarios sacamos un error
            alert("Error al cargar los comentarios.");
        });
    }

    // Función para eliminar el comentario
    // Definimos la funcion dentro de window para poder acceder a ella desde otras partes del codigo
    window.eliminarComentario = function(comentarioId) {

        // Al usar la funcion confirm es como un alert pero con dos botonos aceptar y continuar
        if (confirm('Estás seguro de que deseas eliminar este comentario?')) {

            // Usamos ajax para hacer una peticion DELETE al servidor
            $.ajax({
                url: '/comentarios/' + comentarioId,
                method: 'DELETE',
                // Si no le añadimos este token Laravel bloquea cualquier interaccion con el servidor menos un GET
                data: {
                    _token: '{{ csrf_token() }}'
                },
                // Si todo a salido bien 
                success: function() {
                    cargarComentarios();  // Recargamos los comentarios después de eliminar uno
                    
                    //*******************************************************************************
                    //location.reload(); Tengo que actualizar el boton de comentar de forma asincrona
                    //*******************************************************************************
                },
                // Si algo no a salido bien
                error: function(xhr) { // xhr es una variable de JQuery que contiene informacion sobre los errores
                    alert("Error al eliminar el comentario: " + xhr.responseText);
                }
            });
        }
    };

    // Esto se ejecuta cuando la pagina cargue
    $(document).ready(function() {
        // Cargamos los comentarios 
        cargarComentarios();  
    });


    //**************************************************************/
    //**************************************************************/
    //                            Likes
    //**************************************************************/
    //**************************************************************/

    // Función para dar like
    function darLike(button) {
        const fotoId = button.getAttribute('fotoId'); // ID de la fotografia
        const contadorLikes = document.getElementById('contadorLikes-' + fotoId); // Contador de los likes

        // Usamos JQuery para hacer una solicitud POST la URL es "/fotografias/" + fotoId + "/like"
        $.post("/fotografias/" + fotoId + "/like", {
            _token: '{{ csrf_token() }}' // Este es un token que usa Laravel
        }, 
        function(datos) {
            // La variable datos nos devuelve informacion de los likes desde el servidor

            // Si el like esta dado entonces hacemos cosas
            if (datos.liked) {
                button.querySelector('i').style.color = 'red'; // Cambiamos el color del icono de corazon a rojo
                button.setAttribute('onclick', 'quitarLike(this)'); // Tambien cambiamos la funcionalidad del boton para que al volver a darle quite ellike
            }

            // En el contador de likes metemos el conteo de los likes de esa foto
            contadorLikes.textContent = datos.likesCount;
        })
        // Si algo salio mal damos un error
        .fail(function() {
            alert("Error al dar like.");
        });
    }

    // Función para quitar like
    function quitarLike(button) {
        const fotoId = button.getAttribute('fotoId'); // ID de la fotografia
        const contadorLikes = document.getElementById('contadorLikes-' + fotoId); // Contador de los likes

        // Usamos JQuery para hacer una solicitud POST la URL es "/fotografias/" + fotoId + "/unlike"
        $.post("/fotografias/" + fotoId + "/unlike", {
            _token: '{{ csrf_token() }}' // Este es un token que usa Laravel
        }, function(datos) {
            // La variable datos nos devuelve informacion de los likes desde el servidor

            // Si el like no esta dado entonces hacemos cosas
            if (!datos.liked) {
                button.querySelector('i').style.color = '';
                button.setAttribute('onclick', 'darLike(this)');
            }

            // En el contador de likes metemos el conteo de los likes de esa foto
            contadorLikes.textContent = datos.likesCount;
        })
        // Si algo salio mal damos un error
        .fail(function() {
            alert("Error al quitar el like.");
        });
    }
</script>

@endsection
