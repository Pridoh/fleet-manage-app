<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'vehicle_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_number',
        'vehicle_type_id',
        'brand',
        'model',
        'year',
        'capacity',
        'ownership_type',
        'lease_company',
        'lease_start_date',
        'lease_end_date',
        'location_id',
        'status',
        'last_service_date',
        'next_service_date'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'capacity' => 'integer',
        'lease_start_date' => 'date',
        'lease_end_date' => 'date',
        'last_service_date' => 'date',
        'next_service_date' => 'date',
    ];
    
    /**
     * Get the vehicle type that the vehicle belongs to.
     */
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id', 'vehicle_type_id');
    }
    
    /**
     * Get the location that the vehicle is assigned to.
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }
    
    /**
     * Get the maintenance records for the vehicle.
     */
    public function maintenanceRecords()
    {
        return $this->hasMany(VehicleMaintenance::class, 'vehicle_id');
    }
    
    /**
     * Get the assignments for the vehicle.
     */
    public function assignments()
    {
        return $this->hasMany(VehicleAssignment::class, 'vehicle_id');
    }
    
    /**
     * Scope a query to only include available vehicles.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
    
    /**
     * Scope a query to only include vehicles that need maintenance.
     */
    public function scopeNeedsMaintenance($query)
    {
        return $query->whereDate('next_service_date', '<=', now()->addDays(7));
    }
}
