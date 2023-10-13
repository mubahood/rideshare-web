<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripBooking extends Model
{
    use HasFactory;
    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            //can't create a trip while having another trip in pending status
            $pending_trips = TripBooking::where([
                'status' => 'Pending',
                'driver_id' => $model->driver_id
            ])->first();
            if ($pending_trips) {
                throw new \Exception("You can't create a trip while having another trip in pending status.");
                return false;
            }
            return self::prepare($model);
        });
        //updating
        static::updating(function ($model) {
            return self::prepare($model);
        });
        //created
        static::created(function ($model) {
            //send notification to the driver
            $driver = Administrator::find($model->driver_id);
            if ($driver) {
                Utils::send_message(
                    $driver->phone_number,
                    "RIDESAHRE! You have a new trip booking request. Open the app to view it."
                );
            }
        });
    }
    //prepare
    public static function prepare($model)
    {
        //set the trip text
        $trip = Trip::find($model->trip_id);
        if ($trip) {
            $model->trip_text = $trip->details;
        }
        //set the customer text
        $customer = Administrator::find($model->customer_id);
        if ($customer) {
            $model->customer_text = $customer->name;
        }
        //set the start stage text
        $start_stage = RouteStage::find($model->start_stage_id);
        if ($start_stage) {
            $model->start_stage_text = $start_stage->name;
        }
        //set the end stage text
        $end_stage = RouteStage::find($model->end_stage_id);
        if ($end_stage) {
            $model->end_stage_text = $end_stage->name;
        }

        //scheduled_start_time
        $model->start_time = Utils::format_date($model->start_time);

        return $model;
    }

    //customer relationship
    public function customer()
    {
        return $this->belongsTo(Administrator::class, 'customer_id');
    }
    //driver relationship
    public function driver()
    {
        return $this->belongsTo(Administrator::class, 'driver_id');
    }
    //trip relationship
    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }
}
