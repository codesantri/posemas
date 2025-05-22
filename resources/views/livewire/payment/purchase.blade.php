<div>
<x-filament::button wire:click="paymentProcess" type="button" color="success"
    class="w-full text-[10px] flex items-center justify-center gap-1 my-4" id="pay-button">
    <x-filament::icon icon="heroicon-o-credit-card" class="w-4 h-4 inline-block" />
    <span>Proses Pembayaran</span>
</x-filament::button>
@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script>
    document.addEventListener('livewire:initialized', () => {
            Livewire.on('paying', ({ snapToken }) => {
                console.log(snapToken);
                
                snap.pay(snapToken, {
                    onSuccess: function(result) {
                        Livewire.dispatch('success', result)                       
                    },
                    onPending: function(result) {
                        Livewire.dispatch('pending', result)
                    },
                    onError: function(result) {
                        Livewire.dispatch('error', result)
                    },
                    onClose: function() {
                        Livewire.dispatch('cancel', result)
                    }
                });
            });

            // Livewire.on('payment-error', ({ message }) => {
            //     console.error('Payment error:', message);
            //     alert('Payment Error: ' + message);
            // });
        });
</script>
@endpush
</div>
