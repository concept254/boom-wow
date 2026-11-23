<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(string $action, string $description, ?Model $subject = null, ?int $businessId = null, array $props = []): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'business_unit_id' => $businessId ?? $subject?->business_unit_id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $props ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }
}
