<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;


class PruebasController extends Controller
{
    public function index(){
        $titulo = 'Animales de pana';
        $animales =['perros','gatos','tigres'];
        return view('prueba.index', array(
            'titulo'=>$titulo,
            'animales' => $animales         
        ));
        
         
    }
    
    public function testOrm(){
        
//        $posts = Post::all(); 
//        foreach($posts as $post){
//            echo "<h1>".$post->title. "</h1>";
//            echo "<span style='color:gray;'>{$post->user->name} - {$post->category->name}</span>"
//            echo "<p>".$post->content."</p>";
//            echo '<hr>';
//                    
//        }
        
        $categories = Category::all();//sacar todo los atos de categoria
        foreach($categories as $category){
            echo "<h1>{$category->name}</h1>";
            
            foreach($category->posts as $post){
                echo "<h1>".$post->title. "</h1>";
                echo "<span style='color:gray;'>{$post->user->name} - {$post->category->name}</span>";
                echo "<p>".$post->content."</p>";
                
            }
            echo '<hr>';
        }
        
        die();
        
    }
}
