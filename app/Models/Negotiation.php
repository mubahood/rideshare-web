<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negotiation extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->status = "Active";
            $model->is_active = "Yes";
        });

        //updating
        static::updating(function ($model) {
            if (
                $model->status == 'Accepted' ||
                $model->status == 'Accept' ||
                $model->status == 'Pending' ||
                $model->status == 'Started' ||
                $model->status == 'Ongoing' ||
                $model->status == 'Active'
            ) {
                $model->is_active = 'Yes';
            } else if (
                $model->status == 'Completed' ||
                $model->status == 'Cancelled' ||
                $model->status == 'Canceled' ||
                $model->status == 'Declined'
            ) {
                $model->is_active = 'No';
            }
        });

        //created 
        static::created(function ($model) {
            if ($model->is_active == 'Yes') {
                $driver = User::find($model->driver_id);
                if ($driver != null) {
                    $driver->ready_for_trip = 'No';
                    $driver->save();
                }
            }
        });

        //updated
        static::updated(function ($model) {
            if ($model->is_active == 'Yes') {
                $driver = User::find($model->driver_id);
                if ($driver != null) {
                    $driver->ready_for_trip = 'No';
                    $driver->save();
                }
            }
        });
    }

    //belongs to driver
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    //appends for customer_phone and driver_phone
    protected $appends = ['customer_phone', 'driver_phone'];

    //belongs to customer
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    //get customer phone
    public function getCustomerPhoneAttribute()
    {
        if ($this->customer == null) {
            return null;
        }
        return $this->customer->phone_number;
    }

    //get driver phone
    public function getDriverPhoneAttribute()
    {
        if ($this->driver == null) {
            return null;
        }
        return $this->driver->phone_number;
    }
}
