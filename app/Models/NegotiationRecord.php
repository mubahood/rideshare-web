<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NegotiationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'negotiation_id',
        'customer_id',
        'driver_id',
        'last_negotiator_id',
        'first_negotiator_id',
        'price_accepted',
        'price',
        'message_type',
        'message_body',
        'image_url',
        'audio_url',
        'is_received',
        'is_seen',
        'latitude',
        'longitude'
    ];

    protected $attributes = [
        'price_accepted' => 'No',
        'message_type' => 'Negotiation',
        'is_received' => 'No',
        'is_seen' => 'No'
    ];

    //belongs to negotiation
    public function negotiation()
    {
        return $this->belongsTo(Negotiation::class, 'negotiation_id');
    }

    //belongs to customer
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    //belongs to driver
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    //belongs to last negotiator
    public function lastNegotiator()
    {
        return $this->belongsTo(User::class, 'last_negotiator_id');
    }

    //belongs to first negotiator
    public function firstNegotiator()
    {
        return $this->belongsTo(User::class, 'first_negotiator_id');
    }
}
