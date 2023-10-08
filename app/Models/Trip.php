<?php

namespace App\Models;

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
}
