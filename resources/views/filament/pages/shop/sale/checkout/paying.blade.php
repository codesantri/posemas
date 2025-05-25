<x-filament-panels::page>
    <div class="max-w-xl mx-auto py-10 space-y-6">
        {{-- Section: Title --}}
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900">Konfirmasi Pembayaran</h2>
            <p class="text-sm text-gray-500 mt-1">Pastikan data pembayaran kamu sudah benar ya, bestie 💸</p>
        </div>

        {{-- Section: Total --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border text-center">
            <p class="text-gray-500 mb-2">Total Tagihan</p>
            <h1 class="text-3xl font-bold">Rp {{ number_format($total, 0, ',', '.') }}
            </h1>
        </div>

        {{-- Section: Pay Button --}}
        <div>
            <x-filament::button type="button" color="success" id="pay-button"
                class="w-full py-3 text-lg font-semibold rounded-lg flex justify-center items-center gap-2 transition hover:scale-[1.02]">
                <div wire:loading wire:target="paymentOnline">
                    Memproses...
                </div>
                <span wire:loading.remove wire:target="paymentOnline">
                    Bayar Sekarang
                </span>
            </x-filament::button>
        </div>

        {{-- Section: Note --}}
        <p class="text-xs text-center text-gray-400 mt-6">
            <x-filament::link href="{{ route('filament.admin.shop.resources.sales.orders') }}">Kembali Kehalaman Daftar Pesanan</x-filament::link>
        </p>
    </div>

    @push('scripts')
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-AmD-iAXEoBjanEbo">
    </script>
    <script type="text/javascript">
        document.getElementById('pay-button').onclick = function () {
                snap.pay('{{ $token }}', {
                    onSuccess: function (result) {
                        console.log("Payment Success:", result);
                        // Optionally: window.location.href = '/success';
                    },
                    onPending: function (result) {
                        console.log("Payment Pending:", result);
                    },
                    onError: function (result) {
                        console.error("Payment Error:", result);
                        alert('Terjadi kesalahan saat memproses pembayaran.');
                    }
                });
            };
    </script>
    @endpush
</x-filament-panels::page>