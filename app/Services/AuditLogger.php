<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log a work phase assignment operation.
     */
    public function logAssignment(array $workPhaseIds, int $assignedTo, ?string $notes): void
    {
        Log::channel('audit')->info('Work phases assigned', [
            'action' => 'assign',
            'work_phase_ids' => $workPhaseIds,
            'assigned_to' => $assignedTo,
            'assigned_by' => Auth::id(),
            'notes' => $notes,
            'timestamp' => now()->toIso8601String(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Log a warehouse operation.
     */
    public function logWarehouseOperation(string $operation, array $data, ?string $error = null): void
    {
        $level = $error ? 'error' : 'info';
        
        Log::channel('audit')->$level("Warehouse {$operation}", [
            'action' => "warehouse.{$operation}",
            'data' => $data,
            'error' => $error,
            'user_id' => Auth::id(),
            'timestamp' => now()->toIso8601String(),
            'ip' => request()->ip()
        ]);
    }

    /**
     * Log a work parameter operation.
     */
    public function logWorkParameter(string $operation, int $parameterId, array $data): void
    {
        Log::channel('audit')->info("Work parameter {$operation}", [
            'action' => "work_parameter.{$operation}",
            'parameter_id' => $parameterId,
            'data' => $data,
            'user_id' => Auth::id(),
            'timestamp' => now()->toIso8601String(),
            'ip' => request()->ip()
        ]);
    }

    /**
     * Log a critical error.
     */
    public function logCritical(string $message, array $context = []): void
    {
        Log::critical($message, array_merge($context, [
            'user_id' => Auth::id(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'timestamp' => now()->toIso8601String()
        ]));
    }

    /**
     * Log a security event.
     */
    public function logSecurity(string $event, array $context = []): void
    {
        Log::channel('security')->warning($event, array_merge($context, [
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String()
        ]));
    }

    /**
     * Log an unauthorized access attempt.
     */
    public function logUnauthorized(string $action, array $context = []): void
    {
        $this->logSecurity("Unauthorized access attempt: {$action}", $context);
    }
}
