@extends('layouts.admin')

@section('title', 'Lịch lái thử')

@section('content')
<div class="page-head"><h1>Lịch lái thử</h1></div>

<form class="filters-row" method="get">
    <select class="input" name="status" data-autosubmit>
        <option value="">Mọi trạng thái</option>
        @foreach(\App\Models\TestDrive::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
    </select>
</form>

<div class="card table-wrap table-responsive">
    <table class="table table-hover align-middle">
        <tr><th>Khách hàng</th><th>Xe</th><th>Ngày giờ</th><th>Showroom</th><th>Ghi chú</th><th>Trạng thái</th></tr>
        @forelse($drives as $d)
            <tr>
                <td><b>{{ $d->name }}</b><br><small>{{ $d->phone }} {{ $d->email }}</small></td>
                <td>{{ $d->car?->name }}</td>
                <td>{{ $d->preferred_date->format('d/m/Y') }}<br><small>{{ $d->preferred_time }}</small></td>
                <td><small>{{ $d->showroom }}</small></td>
                <td><small>{{ $d->note }}</small></td>
                <td>
                    <form method="post" action="{{ route('admin.testdrives.update', $d) }}">
                        @csrf @method('patch')
                        <select class="input" name="status" data-autosubmit style="min-width:150px">
                            @foreach(\App\Models\TestDrive::STATUSES as $k => $v)<option value="{{ $k }}" @selected($d->status === $k)>{{ $v }}</option>@endforeach
                        </select>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">Chưa có lịch lái thử.</td></tr>
        @endforelse
    </table>
</div>
{{ $drives->links() }}
@endsection
