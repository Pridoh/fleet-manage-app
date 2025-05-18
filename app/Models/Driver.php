<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'driver_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'license_number',
        'license_type',
        'license_expiry',
        'phone',
        'address',
        'date_of_birth',
        'join_date',
        'status',
        'location_id'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'license_expiry' => 'date',
        'date_of_birth' => 'date',
    ];
    
    /**
     * Get the user associated with the driver.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get the location that the driver is assigned to.
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    
    /**
     * Get the assignments for the driver.
     */
    public function assignments()
    {
        return $this->hasMany(VehicleAssignment::class, 'driver_id');
    }
    
    /**
     * Scope a query to only include available drivers.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
