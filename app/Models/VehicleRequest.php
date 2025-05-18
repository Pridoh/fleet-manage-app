<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleRequest extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'request_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'requester_id',
        'vehicle_type_id',
        'purpose',
        'pickup_location_id',
        'destination_location_id',
        'pickup_datetime',
        'return_datetime',
        'passenger_count',
        'goods_description',
        'priority',
        'status',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pickup_datetime' => 'datetime',
        'return_datetime' => 'datetime',
        'passenger_count' => 'integer'
    ];

    /**
     * Get the user who made the request.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the vehicle type for the request.
     */
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    /**
     * Get the pickup location for the request.
     */
    public function pickupLocation()
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    /**
     * Get the destination location for the request.
     */
    public function destinationLocation()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    /**
     * Get the approval records for the request.
     */
    public function approvals()
    {
        return $this->hasMany(RequestApproval::class, 'request_id');
    }

    /**
     * Get the vehicle assignment for the request.
     */
    public function assignment()
    {
        return $this->hasOne(VehicleAssignment::class, 'request_id');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include completed requests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include partially approved requests.
     */
    public function scopePartiallyApproved($query)
    {
        return $query->where('status', 'partially_approved');
    }

    /**
     * Scope a query to only include requests for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('requester_id', $userId);
    }
}
