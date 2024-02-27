<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
        });


        static::updated(function ($model) {
            TripBooking::where([
                'trip_id' => $model->trip_id
            ])->update([
                'status' => $model->status
            ]);
        });
    }
    //getter for start_stage_text
    public function getStartStageTextAttribute()
    {
        $stage = RouteStage::find($this->start_stage_id);
        if ($stage) {
            return $stage->name;
        }
        return "";
    }
    //do the same for end_stage_text
    public function getEndStageTextAttribute()
    {
        $stage = RouteStage::find($this->end_stage_id);
        if ($stage) {
            return $stage->name;
        }
        return "";
    }
    //do the same for driver_text
    public function getDriverTextAttribute()
    {
        $driver = Administrator::find($this->driver_id);
        if ($driver) {
            return $driver->name;
        }
        return "";
    }

    //getter for driver_contact
    public function getDriverContactAttribute()
    {
        $driver = Administrator::find($this->driver_id);
        if ($driver) {
            return $driver->phone_number_1;
        }
        return "-";
    }
    //get for other_info
    public function getOtherInfoAttribute()
    {
        $driver = Administrator::find($this->driver_id);
        if ($driver) {
            return json_encode($driver);
        }
        return "-";
    }

    protected $appends = [
        'start_stage_text',
        'end_stage_text',
        'driver_text',
        'driver_contact',
        'other_info',
    ];
    /* 
      String price = "";
  String driver_contact = "";
  String other_info = "";
    
    */
}
