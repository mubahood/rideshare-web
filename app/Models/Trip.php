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
            //can't create a trip while having another trip in pending status
            $pending_trips = Trip::where([
                'status' => 'Pending',
                'driver_id' => $model->driver_id
            ])->first();
            if ($pending_trips) {
                throw new \Exception("You can't create a trip while having another trip in pending status.");
                return false;
            }
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
}
