<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['name'];
 
    public function expertProfiles() { return $this->hasMany(ExpertProfile::class); }
}
