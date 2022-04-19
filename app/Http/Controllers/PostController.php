<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct(){
      $this->middleware('api.auth', ['except' => [
        'index',
        'show',
        'getImage',
        'getPostsByCategory',
        'getPostByUser'
      ]]);

    }

    public function index(){
      $posts = Post::all()->load('category');

      return response()->json([
        'code' => 200,
        'status' => 'sucess',
        'posts' => $posts
      ]);

    }

    public function show($id ){

      $post = Post::find($id)->load('category');

      if(is_object($post)){
        $data = array(
          'code' =>200,
          'status' =>'Succes',
          'post' => $post
        );
      }else{
        $data = array(
          'code' =>404,
          'status' =>'Error',
          'message' => ' Error no existe post'
        );

      }
      return response()->json($data, $data['code']);
    }

    public function store(Request $request){
      //con el middleware ya esta autenticado
      //Recoger los datos
      $json = $request ->input('json',true);
      $params = json_decode($json);
      $params_array = json_decode($json,true);

      if(!empty($params_array)){
        //Conseguir usuario identificado
        $user = $this->getIdentity($request);
        //$jwtAuth = new JwtAuth();
        //$token = $request->header('Authorization',null);
        //$user = $jwtAuth->checkToken($token,true); //el true es para que me devuelva el objeto decodificado
        //Validar datos
        $validate = \Validator::make($params_array, [
          'title' =>'required',
          'content'=>'required',
          'category_id' => 'required',
          'image' => 'required'
        ]);
        if ($validate->fails()){
          $data = array(
            'code' =>400,
            'status' =>'Error',
            'message' => 'No se ha guardado el Post, faltan datos'
          );
        }else{
          $post = new Post();
          $post->user_id =$user->sub;
          $post->category_id = $params->category_id;
          $post->title = $params->title;
          $post->content = $params->content;
          $post->image = $params->image;
          $post->save();

          $data = array(
            'code' =>200,
            'status' =>'Success',
            'post' => $post
          );
        }

        //Guardar articulo
      }else{
        $data = array(
          'code' =>400,
          'status' =>'Error',
          'message' => 'Envie bien los datos.'
        );
      }
      //Devolver respuesta
      return response()->json($data,$data['code']);
    }

    public function update($id,Request $request){

      //recoger los datos por POST
      /*$idx = Post::find($id);
      if(!is_object($idx)){
        $data = array(
          'code' =>400 ,
          'status' =>'Error' ,
          'post' =>'id no encontrada'
        );
        return response()->json($data,$data['code']);
      }
      */
      $json = $request->input('json',null);
      $params_array = json_decode($json,true);
      //datos para devolver
      $data = array(
        'code' =>400 ,
        'status' =>'Error' ,
        'post' =>'datos enviado incorrrectamente id no encontrada'
      );


      if(!empty($params_array)){
      //validar los datos
          $validate = \Validator::make($params_array, [
            'title'=>'required',
            'content' => 'required',
            'category_id' =>'required'
          ]);

          if($validate->fails()){
            $data['errors'] = $validate->errors();
            return response()->json($data,$data['code']);
          }

          //eliminar lo que no queremos
          unset($params_array['id']);
          unset($params_array['user_id']);
          unset($params_array['created_at']);
          unset($params_array['user']);

          $user = $this->getIdentity($request);

          //buscar el reistro
          $post = Post::where('id',$id)->where('user_id',$user->sub)->first();

          if(!empty($post) && is_object($post)){

              //acualizar el registro completo
              $post->update($params_array);
              $data = array(
                'code' =>200 ,
                'status' =>'success' ,
                'post' => $post,
                'change' =>$params_array
              );
          }
          /*
          $user = $this->getIdentity($request);
          $where = [
            'id' => $id,
            'user_id' => $user->sub
          ];
          */
      }
      return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request){

      //conseguir el usuario
      $user = $this->getIdentity($request);


      //comprobar si existe registro conseguir el post
      $post = Post::where('id',$id)->where('user_id',$user->sub)->first();

      if(!empty($post)){

        //borrarlo
        $post->delete();
        //devolver algo
        $data = array(
          'code' =>200 ,
          'status' =>'success' ,
          'post' => $post
        );
      }else{
        $data = array(
          'code' =>400 ,
          'status' =>'Error' ,
          'message' => 'Error en eliminar el post'
      );

    }

      return response()->json($data,$data['code']);

    }

    public function upload(Request $request){
      //recoger la imagen de la peticion
      $image = $request->file('file0');

      //Validar imagen
      $validate = \Validator::make($request->all(),[
        'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
      ]);
      //Guardar imagen
      if(!$image || $validate->fails()){
        $data = array(
          'code' =>400 ,
          'status' => 'Error',
          'message' => 'Error al subir la imagen');
      }else{
        $image_name = time().$image->getClientOriginalName();
        \Storage::Disk('images')->put($image_name, \File::get($image));

        $data = array(
          'code' =>200 ,
          'status' =>'success',
          'image' =>$image_name
        );
      }
      //Devolver datos
      return response()->json($data,$data['code']);

    }

    public function getImage($filename){
      //comprobar si existe el fichero
      $isset = \Storage::Disk('images')->exists($filename);

      if($isset){
        //conseguir la imagen
        $file = \Storage::disk('images')->get($filename);

        //devolver la imagen
        return new Response($file,200);
      }else{
        $data = array(
          'code' =>400 ,
          'status'=>'Error',
          'message' => 'La imagen no existe ');
      }
      return response()->json($data,$data['code']);

    }

    public function getPostsByCategory($id){
      $post = Post::where('category_id',$id)->get();

      return response()->json([
        'status' => 'success',
        'post' => $post
      ],200);
    }

    public function getPostByUser($id){
      $post = Post::where('user_id',$id)->get();

      return response()->json([
        'status'=>'success',
        'user' => $post
      ],200);
    }

    private function getIdentity($request){
      $jwtAuth = new JwtAuth();
      $token = $request->header('Authorization');
      $user = $jwtAuth->checkToken($token,true);

      return $user;
    }


}
