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
}
