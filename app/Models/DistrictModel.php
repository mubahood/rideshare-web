<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistrictModel extends Model
{
    protected $table = "district";

    //list of this model to be displayed in the admin panel
    //boot avoid duplicate entry
    protected static function boot()
    {
        parent::boot();

        //deleting
        static::deleting(function ($district) {
            throw new \Exception('Cannot delete this district');
        });

        static::creating(function ($district) {
            //check if the district already exists
            if (DistrictModel::where('name', '=', $district->name)->exists()) {
                return false;
            }
        });
    }

    //ignore the timestamps
    public $timestamps = false;
}
