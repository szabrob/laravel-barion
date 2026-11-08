@php
    $value = $status instanceof \BackedEnum ? $status->value : $status;
    $class = match ($value) {
        'Succeeded' => 'ok',
        'Canceled', 'Failed', 'Expired', 'Rejected', 'Timeout' => 'bad',
        'Reserved', 'Authorized', 'PartiallySucceeded', 'Waiting', 'InProgress' => 'warn',
        default => 'muted',
    };
@endphp
<span class="badge {{ $class }}">{{ $value ?? '–' }}</span>
