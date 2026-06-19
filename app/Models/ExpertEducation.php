<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertEducation extends Model
{
    protected $table = 'expert_education';

    protected $fillable = [
        'expert_profile_id',
        'institution_name',
        'degree',
        'field_of_study',
        'start_year',
        'end_year',
    ];

    public function expertProfile()
    {
        return $this->belongsTo(ExpertProfile::class);
    }
}
