@forelse($attributes as $a)
    @php $val = old("attr.{$a->id}", $values[$a->id] ?? ''); @endphp
    <div class="field">
        <label for="attr-{{ $a->id }}">{{ $a->label() }} @if($a->is_required)<span class="req">*</span>@endif @if($a->is_filterable)<small>(dùng để lọc)</small>@endif</label>
        @if($a->type === 'select')
            <select class="input" id="attr-{{ $a->id }}" name="attr[{{ $a->id }}]">
                <option value="">— Chọn —</option>
                @foreach($a->options ?? [] as $opt)<option value="{{ $opt }}" @selected($val === $opt)>{{ $opt }}</option>@endforeach
            </select>
        @else
            <input class="input" id="attr-{{ $a->id }}" name="attr[{{ $a->id }}]" value="{{ $val }}" @if($a->type === 'number') type="number" step="any" @endif>
        @endif
        @error("attr.{$a->id}")<div class="error">{{ $message }}</div>@enderror
    </div>
@empty
    <p class="muted">Dòng xe này chưa có thông số. Thêm tại mục “Dòng xe, hãng, thông số”.</p>
@endforelse
