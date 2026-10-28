@extends('layouts.admin')

@section('title', 'Nhập / xuất CSV')

@section('content')
<div class="page-head"><h1>Nhập / xuất dữ liệu xe (CSV)</h1></div>

@if(session('import_errors'))
    <div class="alert alert-bad">Các dòng bị bỏ qua:<ul>@foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="dash-grid">
    <div class="card card-body">
        <h3>Xuất CSV</h3>
        <p class="muted">Tải toàn bộ xe (kể cả nháp) kèm thông số kỹ thuật. Tệp UTF-8 có BOM, mở trực tiếp bằng Excel.</p>
        <a class="btn" href="{{ route('admin.io.export') }}">⬇ Tải tệp CSV</a>
    </div>
    <div class="card card-body">
        <h3>Nhập CSV</h3>
        <p class="muted">SKU đã tồn tại sẽ được cập nhật, SKU mới sẽ được tạo. Dòng lỗi được liệt kê và bỏ qua; xe thiếu thông số bắt buộc sẽ tự chuyển về “Chờ duyệt”.</p>
        @error('file')<div class="alert alert-bad">{{ $message }}</div>@enderror
        <form method="post" action="{{ route('admin.io.import') }}" enctype="multipart/form-data" style="display:flex;gap:8px;flex-wrap:wrap">
            @csrf
            <input class="input" type="file" name="file" accept=".csv,text/csv" required style="flex:1">
            <button class="btn btn-dark" type="submit">⬆ Nhập</button>
        </form>
    </div>
</div>

<div class="card card-body" style="margin-top:20px">
    <h3>Định dạng tệp</h3>
    <p>Cột bắt buộc: <code>sku, name, category, price</code>. Thứ tự cột đầy đủ:</p>
    <pre style="background:var(--surface-2);border:1px solid var(--line);padding:12px;border-radius:10px;overflow-x:auto">{{ implode(',', $columns) }}
XE2001,Toyota Raize 1.0 Turbo,SUV / Crossover,Toyota,2025,new,0,Đỏ,498000000,,5,published,SUV đô thị cỡ nhỏ,so_cho=5 chỗ;nhien_lieu=Xăng;hop_so=Số tự động vô cấp CVT;dan_dong=Cầu trước (FWD);dong_co=1.0L Turbo</pre>
    <p class="muted">Cột <code>attributes</code> gồm các cặp <code>mã_thông_số=giá_trị</code> cách nhau bởi dấu chấm phẩy. Mã thông số xem ở trang “Dòng xe, hãng, thông số”. Tệp mẫu: <code>database/sample-import.csv</code>.</p>
</div>
@endsection
