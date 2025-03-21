<!-- Esta es nuestra navBar -->
@extends('master')

<!-- Abrimos una secion donde meteremos el contenido que queramos -->
@section('contenido')

<!-- Aqui mostraremos posibles mensajes de error o de exito -->
@if($message = Session::get('success'))
    <div class="alert alert-success">
        {{ $message }}
    </div>
@endif

@if($message = Session::get('error'))
    <div class="alert alert-danger">
        {{ $message }}
    </div>
@endif

<div class="container-fluid mt-4"> <!-- Se cambió a container-fluid para mejor responsividad -->
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-8 col-lg-6">
            <!-- Comprobamos si En la tabla fotografias existen registros -->
            @if(count($fotografias) > 0)
            <!-- Recorremos cada foto y la imprimimos de la forma que queramos -->
                @foreach($fotografias as $fotografia)
                <div class="card mb-4 w-100 w-md-75 w-lg-50 mx-auto">

                    <!-- Imagen del usuario y su nombre (La imagen la añadire mas adelante) -->
                    <div class="card-header d-flex align-items-center">
                        <!-- <img src="{{ asset('images/user.png') }}" class="rounded-circle me-2" width="40" height="40"> -->
                        <span>Publicaion de: <strong>{{ $fotografia->user->name }}</strong></span>
                    </div>

                    <!-- Aqui metemos la imagen deseada -->
                    <div class="d-flex justify-content-center" style="background-color:#e9ecef;">
                        <a href="{{ route('comentar.index', ['fotografia_id' => $fotografia->id]) }}" class="w-100">
                            <img src="{{ asset('images/' . $fotografia->direccion_imagen) }}" class="card-img-top img-fluid tamano-img" alt="Imagen del usuario">
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <!-- Este es el contenedor de los likes -->
                            <div>
                                <!-- Comprobamos si el usuario ha dado o no like y hacemos cosas en funcion -->
                                <button type="button" role="button" aria-label="Me gusta esta foto" class="btn p-0" onclick="{{ $fotografia->likes()->where('usuario_id', Auth::id())->exists() ? 'quitarLike(this)' : 'darLike(this)' }}" fotoId="{{ $fotografia->id }}">
                                    <i class="fa-solid fa-heart fs-4" id="corazon-{{ $fotografia->id }}" style="{{ $fotografia->likes()->where('usuario_id', Auth::id())->exists() ? 'color: red;' : '' }}"></i>
                                </button>
                                <!-- Aqui aparece el contador de los likes -->
                                <span id="contadorLikes-{{ $fotografia->id }}">{{ $fotografia->likesCount() }}</span>
                            </div>

                            <!-- Este es el boton de comentario -->
                            <form action="{{ route('comentarios.index') }}" method="GET" class="m-0">
                                <input type="hidden" name="fotografia_id" value="{{ $fotografia->id }}">
                                <button type="submit" class="btn p-0" role="button" aria-label="Comentar en esta foto" >
                                    <!-- Combrobamos si el usuario logeado ya ha comentado en la foto -->
                                    <i class="fa-solid fa-comment fs-4" style="{{ \App\Models\Comentarios::comprobarComentario($fotografia->id) ? 'color: #FFD700;' : '' }}"></i>
                                </button>
                                <!-- Aqui aparece el contador de los comentarios -->
                                <span id="contadorComentarios-{{ $fotografia->id }}">{{ $fotografia->comentariosCount() }}</span>
                            </form>

                        </div>

                        <!-- Datos de la foto -->
                        <p class="mb-1"><strong>{{ $fotografia->user->name }}</strong> {{ $fotografia->titulo }}</p>
                        <p class="text-muted">{{ $fotografia->descripcion }}</p>
                    </div>
                </div>
                @endforeach
            @else
                <p class="text-center">No se han encontrado datos</p>
            @endif

            <!-- Con esto Laravel se encarga de generar la paginacion -->
            {!! $fotografias->links() !!}
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

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
                button.setAttribute('onclick', 'quitarLike(this)'); // Tambien cambiamos la funcionalidad del boton para que al volver a darle quite el like
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

@section('css')
<style>
    .card {
        max-width: 80%;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        margin: 0 auto;
    }

    @media (min-width: 768px) {
        .card {
            max-width: 80%;
        }
    }

    @media (min-width: 1024px) {
        .card {
            max-width: 65%;
        }
    }
</style>
@endsection
