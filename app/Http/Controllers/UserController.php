<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "accion de pruebas usercontroller";
    }

    public function register(Request $request){ // son request y post

        //en el frontend se pasara un json con todos los datos del usuario, opr lo que en e back se recogeran el json enviado
        // del frontend
        //recoger los datos de usuario
        $json = $request->input('json',null);
        $params = json_decode($json); // separa la estructura de json y los transforma en un objeto para poder trabajar con php
        $params_array = json_decode($json,true); // esto es un array Recorcar de los array se trabajan con [] mientras que las clases con ->
        //LIMPIAR DATOS

        if(!empty($params) && !empty($params_array)){ // SI LOS DATOS SON DISTINTOS DE NULL
            $params_array = array_map('trim',$params_array); // eliminar espaciados
            //VALIDAR DATOS
            $validate = \Validator::make($params_array,[
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users', // DUPLICACION
                'password'  => 'required'
            ]);

            if ($validate->fails()){
                $data = array(
                    'status' => 'Error',
                    'code' => 404,
                    'message' => 'El usuario no se ha podido crear',
                    'error' => $validate->errors()
                );
            }else{ //los datos son validos

                //CIFRAR CONTRASEÑA
                //$pwd = password_hash($params->password, PASSWORD_BCRYPT,['cost' => 4]); // ESTE CIFRADO GENERABA PASSWORD DISTINTAS
                $pwd = hash('sha256',$params->password);

                //CREAR USUARIO
                $user = new User;
                $user->name=$params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role= 'ROLE_USER';

                //GUARDAR USUARIO
                $user ->save(); //guarda los datos en la base de datos.

                $data = array(
                    'status' => 'Success',
                    'code' => 200,
                    'message'=>'El usuario se ha creado correctamente',
                    'user' => $user
                );
            }
        }else{
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'Los datos enviados no son correcto'
                );

        }

       return response()->json($data, $data['code']);
    }

    public function login(Request $request){

        $JwtAuth = new \JwtAuth();

        //$email = 'Andrescomte@gmail.com';
        //$password = 'lala123';
        //$pwd = hash('sha256',$password);
        //recibir los datos por POST
        $json = $request ->input('json');
        $params = json_decode($json);
        $params_array = json_decode($json,true);

        //validar esos datos
        $validate = \Validator::make($params_array,[
            'email' => 'required|email',
            'password' => 'required',
            //se puede validar nombre tmb
            //o cualquier otro atributo
        ]);
        if ($validate ->fails()){
            $signup = array(
                'status'    => 'error',
                'code'      =>404,
                'message'   => 'El usuario no se a podido identificar',
                'error'     => $validate->errors()
            );
        }else{
            //CIFRAR CONTRASEÑA
            $pwd = hash('sha256', $params->password);
            $signup = $JwtAuth ->signup($params->email, $pwd);
            if(!empty($params->gettoken)){
                $signup = $JwtAuth ->signup($params->email, $pwd,true);
            }

        }

        return response()->json($signup,200);
    }

    public function update(Request $request){

        //COMPROBAR SI EL USUARIO ESTA IDENTIFICADO
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checktoken = $jwtAuth->checkToken($token);
        //SACAR AL USUARIO IDENTIFICADO

        //RECOGER LOS DATOS POR POST
        $json = $request->input('json',NULL);
        $params_array = json_decode($json,true); //trabajr como array

        if ($checktoken && !empty($params_array)){

            $user = $jwtAuth->checkToken($token , true);//como objeto de php

            //VALIDAR DATOS
            $validate = \Validator::make($params_array,[
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users'.$user->sub
            ]);
            //QUITAR CAMPOS QUE NO QUIERO ACTUALIZAR
            unset($params_array['id']);//PARA NO DEJAR ACTUALIZAR EL ATRIBUTO
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //ACTUALIZAR USUARIO EN BD
            $user_update = User::where('id', $user->sub)->update($params_array);

            //DEVOLVER ARRAY CON RESULTADO
            $data = array(
                'status'=>'Succes',
                'code'=> 200,
                'user' => $user,
                'changes' => $params_array
            );

        }else{
            $data = array(
                'status' =>'Error',
                'code' => 404,
                'message' => 'El usuario no esta identificado'
            );
        }
        return response()->json($data,$data['code']);
    }

// carpeta cd ~/.bitnami/stackman/machines/xampp/volumes/root/htdocs para crear el midleware
// un middleware es un metodo que se ejecuta antes que se ejecute la accion de un controlador es como un filtro
// php artisan make:middleware ApiAuthMiddleware (dentro de la carpeta de api-rest-laravel)
    public function upload(Request $request) {

      //recofer datos de la peticion
      $image = $request->file('file0');

      //validacion de imagen
      $validate = \Validator::make($request->all(), [
        'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
      ]);

      //guardar imagen
      if(!$image || $validate->fails()){
        $data = array(
            'status' =>'Error',
            'code' => 404,
            'message' => 'Error al subir imagen '
        );

      }else{
        $image_name = time().$image->getClientOriginalName();
        \Storage::Disk('users')->put($image_name, \File::get($image));

        $data = array(
          'code' => 200,
          'status' => 'Success ',
          'image' => $image_name
        );
      }
      return response()->json($data,$data['code']);
    }

    public function getImage($filename){

      $isset = \Storage::Disk('users')->exists($filename);
      if($isset){
        $file = \Storage::Disk('users')->get($filename);
        return new Response($file,200);

      }else{
        $data = array(
            'status' =>'Error',
            'code' => 404,
            'message' => 'Imagen no existe.'
        );
        return response()->json($data,$data['code']);
      }
    }

    public function detail($id){

      $user = User::find($id);

      if(is_object($user)){
        $data = array(
          'code' =>200 ,
          'Status' => 'Success ',
          'User' => $user
        );
      }else{
        $data = array(
          'code' =>404 ,
          'Status' => 'Error ',
          'message' => 'El usuario no existe '
        );
      }

      return response()->json($data,$data['code']);
    }
}
