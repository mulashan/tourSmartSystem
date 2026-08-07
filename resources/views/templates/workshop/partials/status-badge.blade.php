@php
    $statusLabels = [
        'new' => 'New',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'waiting_parts' => 'Waiting Parts',
        'completed' => 'Completed',
        'invoiced' => 'Invoiced',
        'closed' => 'Closed',
        'cancelled' => 'Cancelled',
    ];

    $statusClasses = [
        'new' => 'bg-primary',
        'assigned' => 'bg-info',
        'in_progress' => 'bg-warning text-dark',
        'waiting_parts' => 'bg-secondary',
        'completed' => 'bg-success',
        'invoiced' => 'bg-dark',
        'closed' => 'bg-light text-dark',
        'cancelled' => 'bg-danger',
    ];
@endphp

<span class="badge {{ $statusClasses[$status] ?? 'bg-secondary' }}">
    {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
</span>
