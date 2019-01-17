<?php

namespace App\Http\Controllers;

use App\ClientePelicula;
use App\Peliculas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PeliculasController extends Controller
{
    public function index()
    {
        //intanciamos el controller donde esta el metodo curl con el cual realizaremos las peticiones
        $curl = new CurlController();
        //url del microservicio findAll que trae toda la lista de peliculas
        $url_findAll = 'http://52.14.94.46:5000/peliculas/findAll';
        //variable para indicar si hubo una busqueda y desplegar un boton en la vista
        $flag = false;

        //le asigno a la variable peliculas el contenido de la peticion al microservicio.
        $peliculas = $curl->curl($url_findAll, null, 'GET');
        if (Input::get('search')) {
            //si hay una busqueda por nombre entrara aqui y ira al microservicio findByNombre
            $input = Input::get('search');
            //armo la url concatenandole el parametro search y el valor que ingresamos en la busqueda
            $url_findByNombre = 'http://52.14.94.46:5000/peliculas/findByNombre?search=' . $input;
            $flag = true;
            $peliculas = $curl->curl($url_findByNombre, null, 'GET');
        }

        //retornamos la lista de peliculas y la variable flag a la vista
        return view('peliculas.index', ['peliculas' => $peliculas, 'flag' => $flag]);
    }

    public function existencia()
    {
        //intanciamos el controller donde esta el metodo curl con el cual realizaremos las peticiones
        $curl = new CurlController();
        //url del microservicio findAll que trae toda la lista de peliculas
        $url_findAll = 'http://52.14.94.46:5000/peliculas/findAll';
        //variable para indicar si hubo una busqueda y desplegar un boton en la vista
        $flag = false;

        //le asigno a la variable peliculas el contenido de la peticion al microservicio.
        $peliculas = $curl->curl($url_findAll, null, 'GET');
        if (Input::get('search')) {
            //si hay una busqueda por nombre entrara aqui y ira al microservicio findByNombre
            $input = Input::get('search');
            //armo la url concatenandole el parametro search y el valor que ingresamos en la busqueda
            $url_findByNombre = 'http://52.14.94.46:5000/peliculas/findByNombre?search=' . $input;
            $flag = true;
            $peliculas = $curl->curl($url_findByNombre, null, 'GET');
        }

        //retornamos la lista de peliculas y la variable flag a la vista
        return view('peliculas.existencia', ['peliculas' => $peliculas, 'flag' => $flag]);
    }

    public function reservar($id)
    {
        $curl = new CurlController();
        $url_findById = 'http://52.14.94.46:5000/peliculas/findById?id=' . $id;

        $peliculas = $curl->curl($url_findById, null, 'GET');

        if (isset($peliculas->id)) {
            $pelicula = Peliculas::where('id', $peliculas->id)->first();
            $pelicula->cantidad = (int)$pelicula->cantidad - 1;
            $pelicula->update();

            $clientePelicula = new ClientePelicula();
            $clientePelicula->id_cliente = Auth::user()->id;
            $clientePelicula->id_pelicula = $pelicula->id;
            $clientePelicula->save();
            Session::put('success', 'La Pelicula ' . $pelicula->nombre . ' fue Recervada Correctamente!');
            return redirect()->route('pelicula');
        }
    }

    public function agregarIndex()
    {
        return view('peliculas.add');
    }

    public function agregarPelicula(Request $request)
    {
        $file = $request->file;
        if ($file->getClientOriginalExtension() == "jpg" || $file->getClientOriginalExtension() == "png" || $file->getClientOriginalExtension() == "jpeg") {
            $destinationPath = public_path() . '/caratulas';
            $fileData = 'caratulas/' . $file->getclientoriginalname();

            $pelicula = new Peliculas();
            $pelicula->nombre = $request->nombre;
            $pelicula->descripcion = $request->descripcion;
            $pelicula->director = $request->director;
            $pelicula->genero = $request->genero;
            $pelicula->precio = $request->precio;
            $pelicula->cantidad = $request->cantidad;
            $pelicula->url_caratula = $fileData;

            try {
                $pelicula->save();
                $file->move($destinationPath, $file->getclientoriginalname());
                Session::put('success', 'Se ha guardado la pelicula correctamente!');
                return redirect()->route('agregarIndex');
            } catch (\Exception $e) {
                Session::put('error', 'Se Produjo un error al subir la pelicula.');
                return redirect()->route('agregarIndex');
            }
        } else {
            Session::put('error', 'Archivo No Valido, Debe tener la extension .JPG, .JPEG O .PNG ');
            return redirect()->route('agregarIndex');
        }
    }
}
