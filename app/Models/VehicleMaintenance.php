<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenance extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'vehicle_maintenance';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'maintenance_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'description',
        'start_date',
        'end_date',
        'odometer_reading',
        'cost',
        'status',
        'performed_by',
        'notes'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'odometer_reading' => 'integer',
        'cost' => 'decimal:2'
    ];
    
    /**
     * Get the vehicle that owns the maintenance record.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    
    /**
     * Scope a query to only include scheduled maintenance.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }
    
    /**
     * Scope a query to only include upcoming maintenance.
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('start_date', '>=', now())
                    ->whereDate('start_date', '<=', now()->addDays(30))
                    ->orderBy('start_date');
    }
}
