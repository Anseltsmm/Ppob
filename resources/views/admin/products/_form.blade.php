<form method="POST" action="{{ $product ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if($product) @method('PUT') @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product?->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Kode Produk OkeConnect</label>
            <input type="text" name="code" class="form-control" value="{{ old('code', $product?->code) }}" placeholder="contoh: T5, S20, BBSDN" required>
            <div class="form-text">Kode produk sesuai daftar produk OkeConnect Anda.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product?->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Tipe Produk</label>
            <select name="type" class="form-select" id="typeSelect" required>
                <option value="prepaid" {{ old('type', $product?->type) === 'prepaid' ? 'selected' : '' }}>Prepaid (harga tetap)</option>
                <option value="opendenom" {{ old('type', $product?->type) === 'opendenom' ? 'selected' : '' }}>Open Denom (nominal bebas)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Operator</label>
            <input type="text" name="operator" class="form-control" value="{{ old('operator', $product?->operator) }}" placeholder="contoh: Telkomsel">
        </div>
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <div class="form-check form-switch mt-2">
                <input type="checkbox" name="status" value="1" class="form-check-input" id="statusSwitch"
                       {{ old('status', $product?->status ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="statusSwitch">Aktif</label>
            </div>
        </div>

        <div class="col-12"><hr></div>

        <div class="col-md-4">
            <label class="form-label">Harga Modal (OkeConnect)</label>
            <input type="number" name="modal_price" class="form-control" step="100" min="0"
                   value="{{ old('modal_price', $product?->modal_price ?? 0) }}" required>
        </div>
        <div class="col-md-4" id="sellPriceBox">
            <label class="form-label">Harga Jual</label>
            <input type="number" name="sell_price" class="form-control" step="100" min="0"
                   value="{{ old('sell_price', $product?->sell_price ?? 0) }}">
            <div class="form-text">Untuk tipe open denom kolom ini diabaikan.</div>
        </div>
        <div class="col-md-4" id="adminFeeBox">
            <label class="form-label">Biaya Admin (Open Denom)</label>
            <input type="number" name="admin_fee" class="form-control" step="100" min="0"
                   value="{{ old('admin_fee', $product?->admin_fee ?? 0) }}">
            <div class="form-text">Ditambahkan ke nominal yang diinput customer.</div>
        </div>

        <div class="col-md-6" id="minNominalBox">
            <label class="form-label">Nominal Minimal (Open Denom)</label>
            <input type="number" name="min_nominal" class="form-control" step="100" min="0"
                   value="{{ old('min_nominal', $product?->min_nominal ?? 10000) }}">
        </div>
        <div class="col-md-6" id="maxNominalBox">
            <label class="form-label">Nominal Maksimal (Open Denom)</label>
            <input type="number" name="max_nominal" class="form-control" step="100" min="0"
                   value="{{ old('max_nominal', $product?->max_nominal ?? 1000000) }}">
        </div>

        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="2">{{ old('description', $product?->description) }}</textarea>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
    const typeSelect = document.getElementById('typeSelect');
    const sellPriceBox = document.getElementById('sellPriceBox');
    const adminFeeBox = document.getElementById('adminFeeBox');
    const minNominalBox = document.getElementById('minNominalBox');
    const maxNominalBox = document.getElementById('maxNominalBox');

    function toggleType() {
        const isOpen = typeSelect.value === 'opendenom';
        sellPriceBox.style.display = isOpen ? 'none' : '';
        adminFeeBox.style.display = isOpen ? '' : 'none';
        minNominalBox.style.display = isOpen ? '' : 'none';
        maxNominalBox.style.display = isOpen ? '' : 'none';
    }

    typeSelect.addEventListener('change', toggleType);
    toggleType();
</script>
@endpush
