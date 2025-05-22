@props(['method'])
@if ($method=='online')

<x-filament::button wire:click.prevent="paymentOnline" type="button" color="success"
    class="w-full text-[10px] flex items-center justify-center gap-1 my-4" id="pay-button">
    <x-filament::icon icon="heroicon-o-credit-card" class="w-4 h-4 inline-block" />
    <span>Proses Pembayaran</span>
</x-filament::button>
@push('scripts')
<!-- Load Midtrans Snap JS with your client key -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.clientKey') }}">
</script>

<script>
    document.addEventListener('livewire:initialized', () => {
            Livewire.on('paying', (snapToken) => {
                snap.pay(snapToken, {
                    onSuccess: function(result) {
                        Livewire.dispatch('payment-status-handler', {result: result});
                    },
                    onPending: function(result) {
                        Livewire.dispatch('payment-status-handler', {result: result});
                    },
                    onError: function(result) {
                        Livewire.dispatch('payment-status-handler', {result: result});
                    },
                    onClose: function() {
                        // Optional: Handle when user closes the popup without payment
                        Livewire.dispatch('payment-popup-closed');
                    }
                });
            });
        });
</script>
@endpush



@else
<x-filament::button type="submit"
    class="w-full text-[10px] flex items-center justify-center gap-1 my-4">
    <x-filament::icon icon="heroicon-o-credit-card" class="w-4 h-4 inline-block" />
    <span>Bayar</span>
</x-filament::button>  
@endif
