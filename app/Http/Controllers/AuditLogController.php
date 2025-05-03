<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by model type
        if ($request->has('model_type')) {
            $query->where('auditable_type', $request->model_type);
        }

        // Filter by event type
        if ($request->has('event')) {
            $query->where('event', $request->event);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $auditLogs = $query->paginate(20);
        
        return view('manager.audit_logs', compact('auditLogs'));
    }
}