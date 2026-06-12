<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['name'];
 
    public function expertProfiles()
    {
        return $this->belongsToMany(ExpertProfile::class, 'expert_skills');
    }
}
