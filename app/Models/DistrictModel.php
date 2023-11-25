<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
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

    //getter for farmer_text
    public function getFarmerTextAttribute()
    {
        //faremer name frommm users
        $farmer = Administrator::where('id', '=', $this->farmer_id)->first();
        if ($farmer == null) {
            $farmer = Administrator::where('id', '=', $this->user_id)->first();
        }
        if ($farmer == null) {
            return "N/A";
        }
        return $farmer->name;
    }

    //getter for seed_text
    public function getSeedTextAttribute()
    {
        //seed name from seeds
        $seed = SeedModel::where('id', '=', $this->seed_id)->first();
        if ($seed == null) {
            return "N/A";
        }
        return $seed->name;
    }
    //appends for farmer_text
    protected $appends = ['farmer_text', 'seed_text',];
}
