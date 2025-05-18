<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestApproval extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'approval_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_id',
        'approver_id',
        'approval_level',
        'status',
        'comments',
        'approval_datetime'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'approval_level' => 'integer',
        'approval_datetime' => 'datetime'
    ];
    
    /**
     * Get the vehicle request that this approval belongs to.
     */
    public function request()
    {
        return $this->belongsTo(VehicleRequest::class, 'request_id');
    }
    
    /**
     * Get the user who is the approver.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
    
    /**
     * Scope a query to only include pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope a query to only include approved approvals.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope a query to only include rejected approvals.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
    
    /**
     * Scope a query to only include approvals for a specific approver.
     */
    public function scopeForApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }
}
