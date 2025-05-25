@push('styles')
<style>
    .input-money {
        font-size: 24px;
        font-weight: bold;
        text-align: right;
        padding-right: 1rem;
        color: #f97e03 !important;
    }

    .change-display {
        font-size: 24px;
        font-weight: bold;
        text-align: right;
        padding-right: 1rem;
        color: #28a745 !important;
        /* Green color for change */
    }
</style>
@endpush

@if ($method!='online')
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.directive('money', (el, { expression }) => {
            const model = expression.trim();

            const formatIDR = (val) => {
                const number = val.toString().replace(/[^\d]/g, '');
                return number ? new Intl.NumberFormat('id-ID').format(number) : '';
            };

            const updateValue = (value) => {
                const formatted = formatIDR(value);
                el.value = formatted;
            };

            if (el.value) {
                updateValue(el.value);
            }

            el.addEventListener('input', (e) => {
                const raw = e.target.value.replace(/[^\d]/g, '');
                e.target.value = formatIDR(raw);

                const component = Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id'));
                if (component && model) {
                    component.set(model, raw ? parseInt(raw) : 0);
                }
            });

            const audio = new Audio('/keyboard.wav');
            el.addEventListener('keyup', (e) => {
                const raw = e.target.value.replace(/[^\d]/g, '');
                e.target.value = formatIDR(raw);
                audio.currentTime = 0;
                audio.play();
            });

            // Auto-update dari Livewire (pas "change" berubah)
            const observer = new MutationObserver(() => {
                updateValue(el.value);
            });

            observer.observe(el, { attributes: true, childList: true, subtree: true });
        });
    });
</script>
@endpush
<div class="w-full my-4">
    <label for="cash" class="block text-sm font-medium mb-2">
        Nominal Pembayaran
    </label>
    <x-filament::input.wrapper prefix="Rp" :valid="! $errors->has('cash')" wire:ignore>
        <x-filament::input id="cash" maxlength="18" name="cash" type="text" inputmode="numeric" wire:model.live="cash" x-data
            x-money="cash" class="input-money w-full rounded-lg" required autofocus />
    </x-filament::input.wrapper>
    @error('cash')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<!-- Kembalian -->
<div class="w-full my-4">
    <label for="change" class="block text-sm font-medium mb-2">
        Kembalian
    </label>
    <x-filament::input.wrapper prefix="Rp">
        <x-filament::input type="text" maxlength="18" value="{{ number_format($change, 0, ',', '.') }}" class="input-money w-full rounded-lg" disabled />
    </x-filament::input.wrapper>
</div>    
@endif