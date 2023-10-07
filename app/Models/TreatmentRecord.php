<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentRecord extends Model
{
    use HasFactory;

    //for patient
    public function patient_user()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    //setter and getter for mulpiple photos
    public function setPhotosAttribute($value)
    {
        $this->attributes['photos'] = json_encode($value);
    }
    //setter and getter for mulpiple photos
    public function getPhotosAttribute($value)
    {
        return $this->attributes['photos'] = json_decode($value);
    }
    //patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    //admmistrator
    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }
}
