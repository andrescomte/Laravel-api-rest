<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';


    // nos sirve para poder acualizar varios datos a la vez 
    protected $fillable = [
        'title','content','category_id'
      ];

    //relacion de uno a muchos inversa(muchos a uno)
    public function user(){
        return $this->belongsTo('App\User','user_id');
    }

    //sacar el objeta que haya relacionado mediente la category id
    public function category(){
        return $this->belongsTo('App\Category','category_id');
    }


}
