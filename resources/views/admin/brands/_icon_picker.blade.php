{{-- ===== Icon Picker (reusable) ===== --}}
{{-- Parameters: $inputId, $pickerId, $selected (nama icon yg aktif, opsional) --}}
@push('styles')
<style>
    .icon-grid {
        display: grid; grid-template-columns: repeat(6, 1fr); gap: .5rem;
        max-height: 220px; overflow-y: auto; padding: .25rem;
    }
    .icon-opt {
        border: 1.5px solid #e2e8f0; border-radius: .55rem;
        width: 100%; aspect-ratio: 1;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: #475569; background: #fff; cursor: pointer;
        transition: all .12s;
    }
    .icon-opt:hover { border-color: #a78bfa; color: #7c3aed; background: #faf5ff; }
    .icon-opt.selected { border-color: #7c3aed; color: #fff; background: #7c3aed; }
</style>
@endpush
@php
    $icons = [
        'sim', 'phone', 'smartphone', 'phone-vibrate', 'phone-flip', 'controller',
        'joystick', 'wifi', 'signal', 'broadcast', 'lightning-charge', 'battery-charging',
        'credit-card', 'wallet2', 'qr-code', 'cash-coin', 'camera', 'music-note-beamed',
        'film', 'tv', 'plug', 'diamond', 'gift', 'piggy-bank', 'bank', 'graph-up-arrow',
        'star', 'gem', 'ticket', 'heart', 'fire', 'cup-hot', 'cloud', 'cart',
        'bag', 'shop', 'tag', 'box', 'arrow-left-right', 'repeat', 'currency-exchange',
    ];
@endphp
@push('scripts')
<script>
    // Icon picker: klik opsi mengisi input target. Idempotent (bind sekali).
    (function () {
        if (window.__iconPickerBound) return;
        window.__iconPickerBound = true;

        document.querySelectorAll('.icon-grid').forEach(function (grid) {
            const inputId = grid.dataset.inputId;
            grid.querySelectorAll('.icon-opt').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    const input = inputId ? document.getElementById(inputId) : null;
                    if (input) input.value = opt.dataset.icon || '';
                    grid.querySelectorAll('.icon-opt').forEach(function (o) { o.classList.remove('selected'); });
                    opt.classList.add('selected');
                });
            });
        });
    })();
</script>
@endpush
<div class="icon-picker mt-2">
    <div class="small fw-semibold text-muted mb-1">Pilih icon (opsyonal):</div>
    <div class="icon-grid" id="{{ $pickerId }}" data-input-id="{{ $inputId }}">
        <button type="button" class="icon-opt {{ empty($selected) ? 'selected' : '' }}" data-icon="" title="Tanpa icon">
            <i class="bi bi-ban"></i>
        </button>
        @foreach($icons as $ic)
            <button type="button" class="icon-opt {{ $selected === $ic ? 'selected' : '' }}" data-icon="{{ $ic }}" title="{{ $ic }}">
                <i class="bi bi-{{ $ic }}"></i>
            </button>
        @endforeach
    </div>
</div>