<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'notification_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'related_to',
        'related_id',
        'is_read'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
    ];
    
    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'info',
        'is_read' => false,
    ];
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
    
    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        $this->is_read = true;
        return $this->save();
    }
    
    /**
     * Create a new info notification.
     */
    public static function info($userId, $title, $message, $relatedTo = null, $relatedId = null)
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => 'info',
            'related_to' => $relatedTo,
            'related_id' => $relatedId
        ]);
    }
    
    /**
     * Create a new success notification.
     */
    public static function success($userId, $title, $message, $relatedTo = null, $relatedId = null)
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => 'success',
            'related_to' => $relatedTo,
            'related_id' => $relatedId
        ]);
    }
    
    /**
     * Create a new warning notification.
     */
    public static function warning($userId, $title, $message, $relatedTo = null, $relatedId = null)
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => 'warning',
            'related_to' => $relatedTo,
            'related_id' => $relatedId
        ]);
    }
    
    /**
     * Create a new error notification.
     */
    public static function error($userId, $title, $message, $relatedTo = null, $relatedId = null)
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => 'error',
            'related_to' => $relatedTo,
            'related_id' => $relatedId
        ]);
    }
} 