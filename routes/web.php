<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FotografiaController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ComentariosController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\CorreoElectronicoController;

    //**************************************************************/
    //**************************************************************/
    //               Rutas para Login y cosas del usuario
    //**************************************************************/
    //**************************************************************/

    Route::get('/', function () {
        //Esto redirige nuestra pagina al login que comprueba si el usuario esta o no logeado
        //Si lo esta entras a la pagina si no te pide que lo hagas
        return redirect()->route('login');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
    
    Route::get('/dashboard', function () {
        return redirect()->route('fotografias.index');;
    })->middleware(['auth', 'verified'])->name('dashboard');

    //**************************************************************/
    //**************************************************************/
    //                  Rutas para fotografias
    //**************************************************************/
    //**************************************************************/
    
    Route::resource('fotografias', FotografiaController::class);

    Route::get('/fotografias/create', [FotografiaController::class, 'create'])->name('fotografias.create');
    Route::get('/mis-fotografias', [FotografiaController::class, 'misFotos'])->name('mis.fotografias')->middleware('auth');

    //**************************************************************/
    //**************************************************************/
    //                  Rutas para los likes
    //**************************************************************/
    //**************************************************************/

    Route::post('/fotografias/{fotografia}/like', [LikeController::class, 'darLike'])
    ->middleware('auth')
    ->name('fotografias.like');

    Route::post('/fotografias/{fotografia}/unlike', [LikeController::class, 'quitarLike'])
    ->middleware('auth')
    ->name('fotografias.unlike');

    //**************************************************************/
    //**************************************************************/
    //                Rutas para los comentarios
    //**************************************************************/
    //**************************************************************/

    Route::resource('comentarios', ComentariosController::class)->except(['show']);

    // Obtenemos todos los comentarios
    Route::get('/comentar', [ComentariosController::class, 'index'])->name('comentar.index');

    // Obtenemos las fotos de una foto seleccionada
    Route::get('/fotografias/{id}/comentarios', [ComentariosController::class, 'getComentarios'])->name('comentarios.get');

    // Ruta para almacenar un nuevo comentario
    Route::post('/comentar', [ComentariosController::class, 'store'])->name('comentar.store');

    //**************************************************************/
    //**************************************************************/
    //                       Rutas para los PDFs
    //**************************************************************/
    //**************************************************************/

    Route::get('/fotografia/{id}/pdf', [PDFController::class, 'generarPDF'])->name('generar.pdf');

    //**************************************************************/
    //**************************************************************/
    //                       Rutas para los correos
    //**************************************************************/
    //**************************************************************/

    Route::post('/enviar-correo', [CorreoElectronicoController::class, 'enviarCorreo'])->name('generar.correo');

    require __DIR__.'/auth.php';
