<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class SystemLogController extends Controller
{
    /**
     * Display a listing of system logs
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Pastikan hanya admin yang bisa mengakses
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses untuk melihat log sistem.');
        }
        
        $query = SystemLog::with('user');
        
        // Filter berdasarkan pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('entity_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter berdasarkan tipe entitas
        if ($request->has('entity_type') && !empty($request->entity_type)) {
            $query->where('entity_type', $request->entity_type);
        }
        
        // Filter berdasarkan aksi
        if ($request->has('action') && !empty($request->action)) {
            $query->where('action', $request->action);
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Ambil daftar tipe entitas dan aksi yang unik untuk filter dropdown
        $entityTypes = SystemLog::select('entity_type')->distinct()->pluck('entity_type')->toArray();
        $actions = SystemLog::select('action')->distinct()->pluck('action')->toArray();
        
        return view('system-logs.index', compact('logs', 'entityTypes', 'actions'));
    }
}
