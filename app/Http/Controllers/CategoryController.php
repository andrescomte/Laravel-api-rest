<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Category;



class CategoryController extends Controller
{

    public function __construct(){
      $this->middleware('api.auth', ['except' => ['index','show']]); // con esta linea se hace uso del middlerware para todos los metodos
                                                                    // excepto para las indicadas dentro del array

    }
    public function index (){

      $categories = Category::all();
      return response()->json([
        'code' => 200,
        'status' => 'success',
        'categories' => $categories
      ]);
    }

    public function show($id){

      $category = Category::find($id);

      if(is_object($category)){
        $data = array(
          'code' => 200 ,
          'status' => 'success',
          'category' => $category
        );
      }else{
        $data = array(
          'code' => 404 ,
          'status' => 'error',
          'message' => 'La categoria no existe.'
        );
      }
      return response()->json($data,$data['code']);
    }

    public function store(Request $request){

      //recoger los datos por Post
      $json = $request->input('json',null);
      $params_array = json_decode($json,true);//el true es para un objeto de php(array)
      //validar los datos

      if (!empty($params_array)){

      $validate = \Validator::make($params_array, [
        'name' => 'required'
      ]);

      //guardar las categorias
      if($validate->fails()){
        $data = array(
          'code' =>400 ,
          'status' => 'Error',
          'message' => 'No se ha guardado la categoria');
      }else{
        $category = new Category(); //utilizacion del modelo Category
        $category->name = $params_array['name'];
        $category->save();
        $data = array(
          'code' =>200 ,
          'status' => 'Success',
          'category' => $category);
      }
    }else{
      $data = array(
        'code' =>400 ,
        'status' => 'Error',
        'message' => 'No se ha ingresado ninguna categoria');
    }
      //devolver resultados
      return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){

      //recoger los datos por Post
      $json = request()->input('json', null);
      $params_array = json_decode($json,true);

      if(!empty($params_array)){
        //validar los datos
        $calidate = \Validator::make($params_array,[
          'name' =>'required'
        ]);
        //quitar lo que no se atualizara
        unset($params_array['id']);
        unset($params_array['created_at']);
        //actualizar la categoria
        $category = Category::where('id',$id)->update($params_array);

        $crated_at = Category::where('id',$id)->first();

        $data = array(
          'code' =>200 ,
          'status'=>'Success',
          'category' => $params_array
        );
      }else{
        $data = array(
          'code' => 400,
          'status' =>'Error',
          'message' => 'No se ha actualizado la categoria ');
      }
      //devolver los datos
      return response()->json($data,$data['code']);






    }
}
