<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'vehicle_type_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type_name',
        'category',
        'description'
    ];
    
    /**
     * Get the vehicles with this vehicle type.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_type_id');
    }

    /**
     * Get type name
     * 
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->type_name;
    }
}
