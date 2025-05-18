<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'location_id';

    /**
     * The name of the "name" column.
     *
     * @var string
     */
    const COLUMN_NAME = 'location_name';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_name',
        'location_type',
        'address',
        'city',
        'province',
        'postal_code'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the vehicles assigned to this location.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'location_id');
    }
    
    /**
     * Get the drivers assigned to this location.
     */
    public function drivers()
    {
        return $this->hasMany(Driver::class, 'location_id');
    }

    /**
     * Get location name
     * 
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->location_name;
    }
}
