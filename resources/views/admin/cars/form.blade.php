@extends('layouts.admin')

@section('title', $car->exists ? 'Sửa xe' : 'Thêm xe')

@section('content')
<div class="page-head">
    <h1>{{ $car->exists ? 'Sửa: '.$car->name : 'Thêm xe mới' }}</h1>
    <div style="display:flex;gap:8px">
        @if($car->exists && $car->status === 'published')<a class="btn btn-ghost" target="_blank" href="{{ route('cars.show', $car->slug) }}">Xem trên cửa hàng</a>@endif
        <a class="btn btn-ghost" href="{{ route('admin.cars.index') }}">← Danh sách</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-bad">Không lưu được, vui lòng kiểm tra:<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="post" enctype="multipart/form-data" action="{{ $car->exists ? route('admin.cars.update', $car) : route('admin.cars.store') }}">
    @csrf
    @if($car->exists) @method('put') @endif

    <div class="form-grid">
        <div>
            <div class="card card-body">
                <h3>Thông tin chung</h3>
                <div class="grid-2">
                    <div class="field"><label>Tên xe *</label><input class="input" name="name" required value="{{ old('name', $car->name) }}" placeholder="VD: Toyota Vios 1.5G CVT"></div>
                    <div class="field"><label>Mã xe (SKU) *</label><input class="input" name="sku" required value="{{ old('sku', $car->sku) }}" placeholder="VD: XE2001"></div>
                    <div class="field"><label>Dòng xe *</label>
                        <select class="input" name="category_id" data-attr-source="{{ route('admin.cars.attributes', '__ID__') }}{{ $car->exists ? '?car='.$car->id : '' }}">
                            @foreach($categories as $c)<option value="{{ $c->id }}" @selected((int) old('category_id', $car->category_id) === $c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field"><label>Hãng xe</label>
                        <select class="input" name="brand_id">
                            <option value="">— Chọn hãng —</option>
                            @foreach($brands as $b)<option value="{{ $b->id }}" @selected((int) old('brand_id', $car->brand_id) === $b->id)>{{ $b->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="grid-3">
                    <div class="field"><label>Năm sản xuất *</label><input class="input" type="number" name="year" required value="{{ old('year', $car->year) }}"></div>
                    <div class="field"><label>Tình trạng *</label>
                        <select class="input" name="condition">@foreach(\App\Models\Car::CONDITIONS as $k => $v)<option value="{{ $k }}" @selected(old('condition', $car->condition) === $k)>{{ $v }}</option>@endforeach</select>
                    </div>
                    <div class="field"><label>Số km đã đi</label><input class="input" type="number" name="mileage" min="0" value="{{ old('mileage', $car->mileage) }}"></div>
                </div>
                <div class="grid-2">
                    <div class="field"><label>Màu xe</label><input class="input" name="color" value="{{ old('color', $car->color) }}" placeholder="VD: Trắng ngọc trai"></div>
                    <div class="field"><label>Mã màu hiển thị</label><input class="input" type="color" name="color_hex" value="{{ old('color_hex', $car->color_hex) }}" style="height:44px;padding:4px"></div>
                </div>
                <div class="field"><label>Mô tả ngắn</label><input class="input" name="short_description" maxlength="255" value="{{ old('short_description', $car->short_description) }}"></div>
                <div class="field"><label>Mô tả chi tiết</label><textarea class="input" name="description" rows="7">{{ old('description', $car->description) }}</textarea></div>
            </div>

            <div class="card card-body" style="margin-top:16px">
                <h3>Thông số kỹ thuật (theo dòng xe)</h3>
                <p class="muted">Các ô bên dưới thay đổi theo dòng xe đang chọn. Thông số có dấu <span class="req">*</span> là bắt buộc để xuất bản.</p>
                <div class="attr-box" data-attr-box>
                    @include('admin.cars._attributes')
                </div>
            </div>
        </div>

        <div>
            <div class="card card-body">
                <h3>Hình ảnh xe thực tế</h3>
                @if($car->imageUrl())
                    <img src="{{ $car->imageUrl() }}" alt="" style="border-radius:10px;width:100%;max-height:220px;object-fit:cover">
                    <label class="check" style="margin-top:8px"><input type="checkbox" name="remove_image" value="1"> Xóa ảnh, dùng hình minh họa SVG</label>
                @else
                    <x-car-art :type="body_type($car->category?->slug)" :color="old('color_hex', $car->color_hex)"/>
                @endif

                <div class="field" style="margin-top:12px">
                    <label>Tải ảnh thật từ máy (≤ 4MB)</label>
                    <input class="input" type="file" name="image" accept="image/*">
                </div>

                <div class="field">
                    <label>Hoặc dán đường dẫn ảnh chính (URL)</label>
                    <input class="input" type="url" name="image_url" value="{{ old('image_url', str_starts_with($car->image ?? '', 'http') ? $car->image : '') }}" placeholder="https://example.com/xe.jpg">
                </div>

                <div class="field">
                    <label>Thư viện ảnh chi tiết (Gallery)</label>
                    <textarea class="input" name="gallery_text" rows="3" placeholder="Mỗi dòng 1 URL ảnh (ảnh góc sau, nội thất, vô lăng...)">{{ old('gallery_text', is_array($car->gallery) ? implode("\n", $car->gallery) : '') }}</textarea>
                    <small class="muted">Hiển thị dạng slide / thumbnails ở trang chi tiết xe.</small>
                </div>
            </div>

            <div class="card card-body" style="margin-top:16px">
                <h3>Giá & kho</h3>
                <div class="field"><label>Giá niêm yết (VND) *</label><input class="input" type="number" name="price" min="1" required value="{{ old('price', $car->price) }}"></div>
                <div class="field"><label>Giá khuyến mãi (VND)</label><input class="input" type="number" name="sale_price" min="1" value="{{ old('sale_price', $car->sale_price) }}"></div>
                <div class="field"><label>Số xe tại đại lý *</label><input class="input" type="number" name="stock" min="0" required value="{{ old('stock', $car->stock) }}"></div>
            </div>

            <div class="card card-body" style="margin-top:16px">
                <h3>Xuất bản</h3>
                <div class="field">
                    <label>Trạng thái</label>
                    <select class="input" name="status">@foreach(\App\Models\Car::STATUSES as $k => $v)<option value="{{ $k }}" @selected(old('status', $car->status) === $k)>{{ $v }}</option>@endforeach</select>
                    <small>Nháp → Chờ duyệt → Đang bán. Chỉ xe “Đang bán” hiển thị ở cửa hàng.</small>
                </div>
                <label class="check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $car->is_featured))> Xe nổi bật (hiện ở trang chủ)</label>
                @if($car->exists)
                    <p style="margin:12px 0 4px"><b>Mức hoàn thiện dữ liệu: {{ $car->completeness }}%</b></p>
                    <div class="meter {{ $car->completeness < 60 ? 'low' : ($car->completeness < 100 ? 'mid' : '') }}"><i style="width:{{ $car->completeness }}%"></i></div>
                    @if($missing = $car->missingRequiredAttributes())<small style="color:var(--bad)">Thiếu: {{ implode(', ', $missing) }}</small>@endif
                @endif
                <button class="btn btn-block" type="submit" style="margin-top:16px">{{ $car->exists ? 'Lưu thay đổi' : 'Thêm xe' }}</button>
            </div>
        </div>
    </div>
</form>
@endsection
