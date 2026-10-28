@extends('layouts.admin')

@section('title', 'Dòng xe, hãng, thông số')

@php
    /** Giá trị hiển thị trong form sửa: nếu chính modal này vừa submit lỗi thì lấy lại dữ liệu cũ (old). */
    $val = fn (string $modal, string $field, $current) => old('_modal') === $modal ? old($field) : $current;
@endphp

@section('content')
<div class="page-head"><h1>Dòng xe, hãng xe & thông số kỹ thuật</h1></div>

@if($errors->any())
    <div class="alert alert-bad alert-dismissible fade show">Không lưu được, vui lòng kiểm tra:
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
@endif

<div class="row g-4">
    {{-- ============ Thêm thông số kỹ thuật (EAV) ============ --}}
    <div class="col-xl-7">
        <div class="card card-body">
            <h3>Thêm thông số kỹ thuật</h3>
            <p class="muted">Mô hình EAV: thêm thông số mới cho một dòng xe chỉ là thêm một dòng dữ liệu – không cần sửa bảng hay mã nguồn.</p>
            <form method="post" action="{{ route('admin.catalog.attributes.store') }}">
                @csrf
                <input type="hidden" name="_modal" value="attr-new">
                @php $isNew = old('_modal') === 'attr-new'; @endphp
                <div class="row g-3">
                    <div class="col-md-6 field mb-0"><label for="na-cat">Dòng xe</label>
                        <select class="input form-select" id="na-cat" name="category_id">@foreach($categories as $c)<option value="{{ $c->id }}" @selected($isNew && (int) old('category_id') === $c->id)>{{ $c->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6 field mb-0"><label for="na-name">Tên thông số</label><input class="input form-control" id="na-name" name="name" required value="{{ $isNew ? old('name') : '' }}" placeholder="VD: Số túi khí"></div>
                    <div class="col-md-6 field mb-0"><label for="na-code">Mã (tự sinh nếu trống)</label><input class="input form-control" id="na-code" name="code" value="{{ $isNew ? old('code') : '' }}" placeholder="so_tui_khi"></div>
                    <div class="col-md-6 field mb-0"><label for="na-type">Kiểu dữ liệu</label>
                        <select class="input form-select" id="na-type" name="type">@foreach(\App\Models\Attribute::TYPES as $k => $v)<option value="{{ $k }}" @selected($isNew && old('type') === $k)>{{ $v }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6 field mb-0"><label for="na-unit">Đơn vị</label><input class="input form-control" id="na-unit" name="unit" value="{{ $isNew ? old('unit') : '' }}" placeholder="VD: km, kWh, mã lực"></div>
                    <div class="col-md-6 field mb-0"><label for="na-opt">Các lựa chọn (kiểu Lựa chọn)</label><input class="input form-control" id="na-opt" name="options" value="{{ $isNew ? old('options') : '' }}" placeholder="Có, Không"></div>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-3">
                    <label class="check mb-0"><input type="checkbox" name="is_required" value="1" @checked($isNew && old('is_required'))> Bắt buộc khi xuất bản</label>
                    <label class="check mb-0"><input type="checkbox" name="is_filterable" value="1" @checked($isNew && old('is_filterable'))> Dùng làm bộ lọc ở cửa hàng</label>
                </div>
                <button class="btn mt-3" type="submit">Thêm thông số</button>
            </form>
        </div>
    </div>

    <div class="col-xl-5 d-flex flex-column gap-4">
        {{-- ============ Dòng xe ============ --}}
        <div class="card card-body">
            <div class="card-title"><h3>Dòng xe</h3><span class="badge">{{ $categories->count() }} dòng</span></div>
            <form method="post" action="{{ route('admin.catalog.categories.store') }}" class="row g-2 mb-3">
                @csrf
                <input type="hidden" name="_modal" value="cat-new">
                <div class="col-sm-5"><input class="input form-control" name="name" required placeholder="Tên dòng xe, VD: MPV" value="{{ old('_modal') === 'cat-new' ? old('name') : '' }}" aria-label="Tên dòng xe"></div>
                <div class="col-sm-4"><input class="input form-control" name="description" placeholder="Mô tả ngắn" value="{{ old('_modal') === 'cat-new' ? old('description') : '' }}" aria-label="Mô tả"></div>
                <div class="col-sm-3 d-grid"><button class="btn btn-sm" type="submit">Thêm</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Tên</th><th class="num">Số xe</th><th class="text-end">Thao tác</th></tr></thead>
                    <tbody>
                    @foreach($categories as $c)
                        <tr>
                            <td><b>{{ $c->name }}</b><br><small>{{ $c->slug }}</small></td>
                            <td class="num">{{ $c->cars_count }}</td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modal-cat-{{ $c->id }}">Sửa</button>
                                @if($c->cars_count === 0)
                                    <form class="inline-form" method="post" action="{{ route('admin.catalog.categories.destroy', $c) }}" data-confirm="Xóa dòng xe “{{ $c->name }}” cùng các thông số của nó?">@csrf @method('delete')<button class="btn btn-ghost btn-sm text-danger" type="submit">Xóa</button></form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============ Hãng xe ============ --}}
        <div class="card card-body">
            <div class="card-title"><h3>Hãng xe</h3><span class="badge">{{ $brands->count() }} hãng</span></div>
            <form method="post" action="{{ route('admin.catalog.brands.store') }}" class="row g-2 mb-3">
                @csrf
                <input type="hidden" name="_modal" value="brand-new">
                <div class="col-sm-5"><input class="input form-control" name="name" required placeholder="Tên hãng" value="{{ old('_modal') === 'brand-new' ? old('name') : '' }}" aria-label="Tên hãng"></div>
                <div class="col-sm-4"><input class="input form-control" name="country" placeholder="Quốc gia" value="{{ old('_modal') === 'brand-new' ? old('country') : '' }}" aria-label="Quốc gia"></div>
                <div class="col-sm-3 d-grid"><button class="btn btn-sm" type="submit">Thêm</button></div>
            </form>
            <div class="table-responsive" style="max-height:320px">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Hãng</th><th>Quốc gia</th><th class="num">Số xe</th><th class="text-end">Thao tác</th></tr></thead>
                    <tbody>
                    @foreach($brands as $b)
                        <tr>
                            <td><b>{{ $b->name }}</b></td>
                            <td>{{ $b->country ?: '—' }}</td>
                            <td class="num">{{ $b->cars_count }}</td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modal-brand-{{ $b->id }}">Sửa</button>
                                <form class="inline-form" method="post" action="{{ route('admin.catalog.brands.destroy', $b) }}" data-confirm="Xóa hãng {{ $b->name }}? Các xe của hãng sẽ không còn gắn hãng.">@csrf @method('delete')<button class="btn btn-ghost btn-sm text-danger" type="submit">Xóa</button></form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ============ Thông số theo từng dòng xe ============ --}}
@foreach($categories as $c)
    <div class="card mt-4">
        <div class="card-body pb-0">
            <h3 class="mb-1">{{ $c->name }}</h3><small>{{ $c->cars_count }} xe · {{ $c->description }}</small>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle">
                <thead><tr><th>Mã</th><th>Thông số</th><th>Kiểu</th><th>Lựa chọn</th><th>Bắt buộc</th><th>Lọc được</th><th class="text-end">Thao tác</th></tr></thead>
                <tbody>
                @forelse($c->attributes as $a)
                    <tr>
                        <td><code>{{ $a->code }}</code></td>
                        <td>{{ $a->label() }}</td>
                        <td>{{ \App\Models\Attribute::TYPES[$a->type] }}</td>
                        <td><small>{{ implode(', ', $a->options ?? []) }}</small></td>
                        <td>{!! $a->is_required ? '<span class="badge badge-ok">Có</span>' : '<span class="badge">Không</span>' !!}</td>
                        <td>{!! $a->is_filterable ? '<span class="badge badge-info">Có</span>' : '<span class="badge">Không</span>' !!}</td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modal-attr-{{ $a->id }}">Sửa</button>
                            <form class="inline-form" method="post" action="{{ route('admin.catalog.attributes.destroy', $a) }}" data-confirm="Xóa thông số {{ $a->name }}? Giá trị của mọi xe cũng bị xóa.">@csrf @method('delete')<button class="btn btn-ghost btn-sm text-danger" type="submit">Xóa</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Chưa có thông số nào.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach

{{-- ============ Modal sửa (Bootstrap) ============ --}}
@foreach($categories as $c)
    @php $m = 'cat-'.$c->id; @endphp
    <div class="modal fade" id="modal-{{ $m }}" tabindex="-1" aria-labelledby="title-{{ $m }}" aria-hidden="true" @if(old('_modal') === $m) data-open-on-load @endif>
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="{{ route('admin.catalog.categories.update', $c) }}">
                @csrf @method('put')
                <input type="hidden" name="_modal" value="{{ $m }}">
                <div class="modal-header"><h5 class="modal-title" id="title-{{ $m }}">Sửa dòng xe “{{ $c->name }}”</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button></div>
                <div class="modal-body">
                    <div class="field"><label for="{{ $m }}-name">Tên dòng xe</label><input class="input form-control" id="{{ $m }}-name" name="name" required value="{{ $val($m, 'name', $c->name) }}">@if(old('_modal') === $m)@error('name')<div class="error">{{ $message }}</div>@enderror @endif</div>
                    <div class="field"><label for="{{ $m }}-desc">Mô tả ngắn</label><input class="input form-control" id="{{ $m }}-desc" name="description" value="{{ $val($m, 'description', $c->description) }}"></div>
                    <div class="field"><label for="{{ $m }}-sort">Thứ tự hiển thị</label><input class="input form-control" id="{{ $m }}-sort" type="number" min="0" name="sort_order" value="{{ $val($m, 'sort_order', $c->sort_order) }}"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button><button class="btn" type="submit">Lưu thay đổi</button></div>
            </form>
        </div>
    </div>
@endforeach

@foreach($brands as $b)
    @php $m = 'brand-'.$b->id; @endphp
    <div class="modal fade" id="modal-{{ $m }}" tabindex="-1" aria-labelledby="title-{{ $m }}" aria-hidden="true" @if(old('_modal') === $m) data-open-on-load @endif>
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="{{ route('admin.catalog.brands.update', $b) }}">
                @csrf @method('put')
                <input type="hidden" name="_modal" value="{{ $m }}">
                <div class="modal-header"><h5 class="modal-title" id="title-{{ $m }}">Sửa hãng xe “{{ $b->name }}”</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button></div>
                <div class="modal-body">
                    <div class="field"><label for="{{ $m }}-name">Tên hãng</label><input class="input form-control" id="{{ $m }}-name" name="name" required value="{{ $val($m, 'name', $b->name) }}">@if(old('_modal') === $m)@error('name')<div class="error">{{ $message }}</div>@enderror @endif</div>
                    <div class="field"><label for="{{ $m }}-country">Quốc gia</label><input class="input form-control" id="{{ $m }}-country" name="country" value="{{ $val($m, 'country', $b->country) }}"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button><button class="btn" type="submit">Lưu thay đổi</button></div>
            </form>
        </div>
    </div>
@endforeach

@foreach($categories as $c)
    @foreach($c->attributes as $a)
        @php $m = 'attr-'.$a->id; @endphp
        <div class="modal fade" id="modal-{{ $m }}" tabindex="-1" aria-labelledby="title-{{ $m }}" aria-hidden="true" @if(old('_modal') === $m) data-open-on-load @endif>
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form class="modal-content" method="post" action="{{ route('admin.catalog.attributes.update', $a) }}">
                    @csrf @method('put')
                    <input type="hidden" name="_modal" value="{{ $m }}">
                    <div class="modal-header"><h5 class="modal-title" id="title-{{ $m }}">Sửa thông số “{{ $a->name }}” · {{ $c->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button></div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6 field mb-0"><label for="{{ $m }}-name">Tên thông số</label><input class="input form-control" id="{{ $m }}-name" name="name" required value="{{ $val($m, 'name', $a->name) }}"></div>
                            <div class="col-md-6 field mb-0"><label for="{{ $m }}-code">Mã</label><input class="input form-control" id="{{ $m }}-code" name="code" required value="{{ $val($m, 'code', $a->code) }}"></div>
                            <div class="col-md-6 field mb-0"><label for="{{ $m }}-type">Kiểu dữ liệu</label>
                                <select class="input form-select" id="{{ $m }}-type" name="type">@foreach(\App\Models\Attribute::TYPES as $k => $v)<option value="{{ $k }}" @selected($val($m, 'type', $a->type) === $k)>{{ $v }}</option>@endforeach</select>
                            </div>
                            <div class="col-md-6 field mb-0"><label for="{{ $m }}-unit">Đơn vị</label><input class="input form-control" id="{{ $m }}-unit" name="unit" value="{{ $val($m, 'unit', $a->unit) }}"></div>
                            <div class="col-12 field mb-0"><label for="{{ $m }}-opt">Các lựa chọn (cách nhau bằng dấu phẩy)</label><input class="input form-control" id="{{ $m }}-opt" name="options" value="{{ $val($m, 'options', implode(', ', $a->options ?? [])) }}"></div>
                        </div>
                        <div class="d-flex flex-wrap gap-3 mt-3">
                            <label class="check mb-0"><input type="checkbox" name="is_required" value="1" @checked($val($m, 'is_required', $a->is_required))> Bắt buộc khi xuất bản</label>
                            <label class="check mb-0"><input type="checkbox" name="is_filterable" value="1" @checked($val($m, 'is_filterable', $a->is_filterable))> Dùng làm bộ lọc ở cửa hàng</label>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button><button class="btn" type="submit">Lưu thay đổi</button></div>
                </form>
            </div>
        </div>
    @endforeach
@endforeach
@endsection
