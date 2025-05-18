<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'setting_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
        'updated_by'
    ];
    
    /**
     * Get the user that last updated this setting.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        
        return $setting ? $setting->setting_value : $default;
    }
}
