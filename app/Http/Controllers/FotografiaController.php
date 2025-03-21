<?php

namespace App\Http\Controllers;

// Este es el modelo que usaremos en este controlador
use App\Models\Fotografia;

use Illuminate\Http\Request; // Esto nos permitira interactuar con los datos enviados desde un formulario
use Illuminate\Support\Facades\Auth; // Este nos servira para realizar autenticaciones del usuario

class FotografiaController extends Controller
{

    //**************************************************************/
    //**************************************************************/
    //                Visualizamos las fotografias
    //**************************************************************/
    //**************************************************************/

    // Funcion para mostrar la vista de comentarios
    public function index()
    {
        // La funcion check() se usa para comprobar si el usuario esta logueado
        if (Auth::check()) {
            // Obtenemos las fotografias con sus relaciones correspondientes
            $fotografias = Fotografia::with('user', 'likes', 'comentarios')->orderBy('id', 'desc')->paginate(5); // Las paginamos de 5 en 5

            // Devolvemos la vista deseada y con el compact() le pasamos a esta misma vista $fotografias
            // El request se encarga de calcular el indice para las fotografias
            return view('index', compact('fotografias'))->with('i', (request()->input('page', 1) - 1) * 5);
        } 
        else {
            return redirect('/'); // Si el usuario no esta logueado se le manda a la pagina de login
        }
    }

    // Funcion que se encarga de devolver solamente las publicaiones del usuario logeado
    public function misFotos() {
        // Buscamos las fotografias del usuario logeado y si no tiene devolvemos una coleccion 
        // Vacia para que la pagina siga funcionando sin dar error
        // El operador ?? sirve para que php combruebe si el valor pasado el null
        $misFotografias = Auth::user()->fotografias ?? collect(); 
        return view('mis_fotografias', compact('misFotografias'));
    }   

    //**************************************************************/
    //**************************************************************/
    //                Crear y guardar fotografias
    //**************************************************************/
    //**************************************************************/

    // Esta funcion unicamente nos va a redireccionar a la vista create
    public function create()
    {
        return view('create');
    }

    // Esta funcion es la encargada de crear y guardar una nueva fotografia
    public function store(Request $request)
    {
        // Usamos la funcion validate() para comprobar que los daton enviados por el $request cumplen los requisitos
        $request->validate([
            'usuario_id' => 'required',
            'direccion_imagen' => 'required|image|mimes:jpg,png,jpeg,gif,svg', // Se puede enviar cualquiera de estos tipos de archivo
            'titulo' => 'required|max:255',
            'descripcion' => 'required'
        ]);

        // Estamos definiendo el nombre de la variable con la que guardaremos el archivo
        // Al estar usando el time() nos aseguramos de que cada archivo tiene un nombre diferente
        // y obtenemos la extension con getClientOriginalExtension()
        // El nombre del archivo quedaria algo asi "1633105600.jpg"
        $file_name = time() . '.' . $request->direccion_imagen->getClientOriginalExtension();

        // con move() movemos el archivo a la ruta especificada
        $request->direccion_imagen->move(public_path('images'), $file_name);

        // Creamos la nueva fotografia con sus datos correspondientes
        $fotografia = new Fotografia;
        $fotografia->usuario_id = $request->usuario_id;
        $fotografia->direccion_imagen = $file_name;
        $fotografia->titulo = $request->titulo;
        $fotografia->descripcion = $request->descripcion;
        // Con la funcion save() se guarda en la base de datos nuestra nueva foto
        $fotografia->save();

        // Redirijimos a la vista de todas las fotografias con un mensaje de exito
        return redirect('fotografias')->with('success', 'Se ha subido la imagen con éxito !!');
    }
}
