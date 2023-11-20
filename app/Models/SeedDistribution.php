<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeedDistribution extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        //deleting
        static::deleting(function ($seed_distribution) {
            throw new \Exception('Cannot delete this seed distribution');
        });
        static::updating(function ($seed_distribution) {

            throw new \Exception('Cannot delete this seed distribution');
        });

        static::creating(function ($m) {
            //check if the seed distribution already exists
            $farmer = Administrator::find($m->farmer_id);
            if ($farmer == null) {
                throw new \Exception('Farmer does not exist.');
            }
            if ($farmer->otp != $m->otp) {
                throw new \Exception('OTP does not match.');
            }
            unset($m->otp);
            $farmer->otp = null;
            $farmer->save();
        });
    }

    public function user()
    {
        return $this->belongsTo(Administrator::class, 'user_id');
    }
}
