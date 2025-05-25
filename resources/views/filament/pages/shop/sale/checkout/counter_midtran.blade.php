@push('styles')
<style>
    .input-money {
    font-size: 24px;
    font-weight: bold;
    /* text-align: right; */
    padding-right: 1rem;
    color: #f97e03 !important;
    }
</style>
@endpush

@push('scripts')
<script>
   document.addEventListener('alpine:init', () => {
            Alpine.directive('money', (el, { expression }) => {
            const model = expression.trim();
        const format = (val) => {
        const num = val.replace(/[^\d]/g, '');
        return num ? new Intl.NumberFormat('id-ID').format(num) : '';
        };
    if (el.value) {
    el.value = format(el.value);
    }

    el.addEventListener('input', (e) => {
    const raw = e.target.value.replace(/[^\d]/g, '');
    e.target.value = format(raw);

    const component = Livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id'));
        if (component && model) {
            component.set(model, raw ? parseInt(raw) : 0);
        }
    });

        const audio = new Audio('/keyboard.wav');
        el.addEventListener('keyup', (e) => {
            const raw = e.target.value.replace(/[^\d]/g, '');
            e.target.value = raw ? format(raw) : '';
            audio.currentTime = 0;
            audio.play();
        });
    });
});

</script>
@endpush
{{-- {{ dd($payment) }} --}}
<!-- Nominal Pembayaran -->
@if($payment_method === 'cash')
<div class="w-full my-4">
    <label for="cash" class="block text-sm font-medium mb-2">
        Nominal Pembayaran
    </label>
    <x-filament::input.wrapper prefix="Rp" :valid="! $errors->has('cash')" wire:ignore>
        <x-filament::input id="cash" name="cash" type="text" inputmode="numeric" wire:model.live="cash" x-data
            x-money="cash" class="input-money w-full rounded-lg" required autofocus />
    </x-filament::input.wrapper>
    @error('cash')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
@endif
<!-- Diskon -->
<div class="w-full my-4">
    <label for="discount" class="block text-sm font-medium mb-2">
        Diskon
    </label>

    <x-filament::input.wrapper prefix="Rp" :valid="! $errors->has('discount')">
        <x-filament::input id="discount" name="discount" wire:ignore type="text" inputmode="numeric" wire:model.live="discount" x-data
            x-money="discount" class="input-money w-full rounded-lg" />
    </x-filament::input.wrapper>

    @error('discount')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>


