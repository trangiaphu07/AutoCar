@props(['order'])
@php
    $s = ['pending' => 'badge-warn', 'confirmed' => 'badge-info', 'delivering' => 'badge-info', 'completed' => 'badge-ok', 'cancelled' => 'badge-bad'][$order->status] ?? '';
    $p = ['unpaid' => '', 'deposited' => 'badge-info', 'paid' => 'badge-ok', 'failed' => 'badge-bad'][$order->payment_status] ?? '';
@endphp
<span class="badge {{ $s }}">{{ $order->statusLabel() }}</span>
<span class="badge {{ $p }}">{{ $order->paymentStatusLabel() }}</span>
