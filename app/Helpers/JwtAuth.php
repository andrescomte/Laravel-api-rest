<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User; //ORM

class JwtAuth{

    public function __construct() {
        $this->key= 'esto_es_una_clave_super_secreta-12345679';
    }

    public function signup($email, $password, $getToken = null){

        //buscar si existe el usuario con las credenciales
       $user = User::Where([
            'email'=>$email,
            'password'=>$password
        ])->first(); // retorna el primero que encuentra

        // comprobar si son correctos
        $signup = false;
        if(is_object($user)){
            $signup = true;
        }
        //Generara el token con los datos del usuario
        if($signup){
            $token = array(
                'sub' => $user->id,
                'email' =>$user->email,
                'name' =>$user->name,
                'surname'=>$user->surname,
                'iat' => time(), //tiempo registro
                'exp' => time()+ (24*7*60*60)//tiempo de expiracion
            );

            $jwt = JWT::encode($token, $this->key,'HS256');
            $decoded = JWT::decode($jwt, $this->key,['HS256']);

            //Devlver los datos decodificados o el token en funcion de un parametro
            if(is_null($getToken)){
                $data = $jwt;

            }else{
                $data = $decoded;
            }

        }else{
            $data = array(
                'status' => 'Error',
                'message' => 'Login incorrecto'
            );
        }
       return $data;
    }

    public function checkToken($jwt , $getIdentity = false){
        $auth = false;

        try{
            $jwt = str_replace('"','',$jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        }catch(\UnexpectedValueException $e) {
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }
        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth= false;
        }
        if($getIdentity){ // si es true
            return $decoded;
        }

        return $auth;


    }


}
