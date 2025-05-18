<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleUsageLog extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'log_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assignment_id',
        'log_type',
        'location_id',
        'odometer_reading',
        'fuel_level',
        'fuel_added',
        'fuel_cost',
        'notes',
        'logged_by',
        'log_datetime'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'log_datetime' => 'datetime',
        'fuel_level' => 'decimal:2',
        'fuel_added' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
    ];

    /**
     * The model only has a created_at timestamp.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the vehicle assignment associated with the log.
     */
    public function assignment()
    {
        return $this->belongsTo(VehicleAssignment::class, 'assignment_id');
    }

    /**
     * Get the location associated with the log.
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Get the user who created the log.
     */
    public function logger()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
