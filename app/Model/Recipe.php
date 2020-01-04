<?php

namespace App\Model;


class Recipe extends BaseModel
{
    protected $table = 'recipe';
    
    protected $fillable = ['plucode', 'shortname', 'longname', 'description'];
    
}
