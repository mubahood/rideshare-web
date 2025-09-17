<?php

namespace App\Models;

use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;



class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;

    protected $table = "admin_users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name', 
        'name',
        'email',
        'phone_number',
        'username',
        'password',
        'sex',
        'date_of_birth',
        'place_of_birth',
        'home_address',
        'current_address',
        'user_type',
        'status',
        'nin',
        'driving_license_number',
        'driving_license_issue_date',
        'driving_license_validity',
        'driving_license_issue_authority',
        'driving_license_photo',
        'automobile',
        
        // Service capability fields
        'is_car',
        'is_boda',
        'is_ambulance',
        'is_police',
        'is_delivery',
        'is_breakdown',
        'is_firebrugade',
        
        // Service approval fields
        'is_car_approved',
        'is_boda_approved',
        'is_ambulance_approved',
        'is_police_approved',
        'is_delivery_approved',
        'is_breakdown_approved',
        'is_firebrugade_approved',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'driving_license_issue_date' => 'date',
        'driving_license_validity' => 'date',
        'date_of_birth' => 'date',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Check if user has specific service capability
     */
    public function hasServiceCapability($service)
    {
        $field = "is_{$service}";
        return isset($this->$field) && $this->$field === 'Yes';
    }

    /**
     * Check if user's service is approved
     */
    public function isServiceApproved($service)
    {
        $field = "is_{$service}_approved";
        return isset($this->$field) && $this->$field === 'Yes';
    }

    /**
     * Get all user's approved services
     */
    public function getApprovedServices()
    {
        $services = ['car', 'boda', 'ambulance', 'police', 'delivery', 'breakdown', 'firebrugade'];
        $approved = [];
        
        foreach ($services as $service) {
            if ($this->isServiceApproved($service)) {
                $approved[] = $service;
            }
        }
        
        return $approved;
    }

    /**
     * Get all user's requested services
     */
    public function getRequestedServices()
    {
        $services = ['car', 'boda', 'ambulance', 'police', 'delivery', 'breakdown', 'firebrugade'];
        $requested = [];
        
        foreach ($services as $service) {
            if ($this->hasServiceCapability($service)) {
                $requested[] = $service;
            }
        }
        
        return $requested;
    }


    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function programs()
    {
        return $this->hasMany(UserHasProgram::class, 'user_id');
    }
}
