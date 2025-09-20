<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'driver_id',
        'customer_id', 
        'start_stage_id',
        'end_stage_id',
        'scheduled_start_time',
        'scheduled_end_time', 
        'start_time',
        'end_time',
        'status',
        'vehicel_reg_number',
        'slots', // Add slots to fillable array
        'details',
        'car_model',
        'price',
        'start_gps',
        'end_pgs',
        'start_name',
        'end_name',
        'start_address',
        'end_address',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'slots' => 'integer', // Ensure slots is cast as integer
        'price' => 'decimal:2',
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

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
            return $driver->phone_number;
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

    /**
     * Relationships
     */
    public function driver()
    {
        return $this->belongsTo(Administrator::class, 'driver_id');
    }

    public function customer()
    {
        return $this->belongsTo(Administrator::class, 'customer_id');
    }

    public function startStage()
    {
        return $this->belongsTo(RouteStage::class, 'start_stage_id');
    }

    public function endStage()
    {
        return $this->belongsTo(RouteStage::class, 'end_stage_id');
    }

    public function bookings()
    {
        return $this->hasMany(TripBooking::class);
    }
    /* 
      String price = "";
  String driver_contact = "";
  String other_info = "";
    
    */
}
