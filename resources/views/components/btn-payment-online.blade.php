<x-filament::button type="button" color="success" wire:loading.attr="disabled" wire:target="paymentProcess" {{-- Replace
    with your actual Livewire method name --}}
    class="w-full text-white font-semibold py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center justify-center gap-2">
    <div wire:loading wire:target="paymentProcess">
        Memproses...
    </div>
    <span wire:loading.remove wire:target="paymentProcess">
        Bayar Rp {{ number_format($totalPayment, 0, ',', '.') }}
    </span>
</x-filament::button>