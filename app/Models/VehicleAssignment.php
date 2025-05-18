<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleAssignment extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'assignment_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_id',
        'vehicle_id',
        'driver_id',
        'assigned_by',
        'actual_start_datetime',
        'actual_end_datetime',
        'start_odometer',
        'end_odometer',
        'fuel_used',
        'status',
        'notes'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'actual_start_datetime' => 'datetime',
        'actual_end_datetime' => 'datetime',
        'start_odometer' => 'integer',
        'end_odometer' => 'integer',
        'fuel_used' => 'decimal:2',
    ];
    
    /**
     * Get the request that owns the assignment.
     */
    public function request()
    {
        return $this->belongsTo(VehicleRequest::class, 'request_id');
    }
    
    /**
     * Get the vehicle assigned.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    
    /**
     * Get the driver assigned.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    
    /**
     * Get the user who assigned the vehicle.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    
    /**
     * Get the usage logs for this assignment.
     */
    public function usageLogs()
    {
        return $this->hasMany(VehicleUsageLog::class, 'assignment_id');
    }
}
