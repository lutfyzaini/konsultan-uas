<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertCertification extends Model
{
    protected $table = 'expert_certifications';

    protected $fillable = [
        'expert_profile_id',
        'certification_name',
        'issuing_organization',
        'issued_year',
    ];

    public function expertProfile()
    {
        return $this->belongsTo(ExpertProfile::class);
    }
}
