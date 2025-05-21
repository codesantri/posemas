@props(['state' => [], 'record' => null , 'discount'=>0,'change'=>0, 'total'=>0])
<div class="w-[360px]  flex flex-col">
    <div class="flex-1 overflow-y-auto px-6 py-5">
        <ul class="space-y-6">
            @foreach($state->details as $item)
            <li class="flex items-center justify-between space-x-4">
                <div class="flex items-center justify-between space-x-4">
                    <img alt="{{ $item->product->name }}" class="w-24 h-24 rounded border object-cover flex-shrink-0"
                        height="85" src="{{ $item->product->image }}" width="85" />
                    <div class="flex-1 min-w-0 mx-3">
                        <p class="font-semibold text-sm truncate mb-2">
                            {{ $item->product->name ?? 'Produk tidak ditemukan' }}
                        </p>
                        <div class="flex flex-row items-start">
                            <x-filament::button disabled color="light" class="rounded-none" size="sm"
                                style="color: rgb(108, 102, 102);">
                                <x-slot name="badge" color="success">
                                    <strong>x{{ $item->quantity }}</strong>
                                </x-slot>
                                {{ 'Rp.'. number_format($item->buy_price, 0, ',', '.') }}
                            </x-filament::button>
                        </div>
                        <div class="flex flex-row items-start">
                            <x-filament::badge color="success" class="mx-1">
                                {{ $item->product->karat->karat }}
                            </x-filament::badge>
                            <x-filament::badge color="info">
                                {{ $item->weight .'g' }}
                            </x-filament::badge>
                        </div>
                    </div>

                </div>
                <div class="flex flex-col items-end space-y-1">
                    <p class="font-semibold text-gray-900 text-sm">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </p>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="border-t border-gray-200 px-6 py-5">
        <div class="flex justify-between font-semibold mb-4">
            <span>Subtotal</span>
            <span>Rp {{ number_format($state->total_amount, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between font-semibold mb-1">
            <span>Diskon</span>
            <strong>-Rp {{ number_format($discount, 0, ',', '.') }}</strong>
        </div>

        <div class="flex justify-between font-semibold mb-1">
            <span>Kembalian</span>
            <strong>Rp {{ number_format($change, 0, ',', '.') }}</strong>
        </div>

        <div class="flex justify-between font-semibold mb-1">
            <span>Total Pembayaran</span>
            <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
        </div>

        <p class="text-xs text-gray-500 mb-4">
            PPN dan biaya lainnya dihitung saat checkout.
        </p>
        <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="checkout" {{-- Replace with your
            actual Livewire method name --}}
            class="w-full text-white font-semibold py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center justify-center gap-2">
            <div wire:loading wire:target="checkout">
                Memproses...
            </div>
            <span wire:loading.remove wire:target="checkout">
                Bayar Sekarang
            </span>
        </x-filament::button>
        <p class="text-center text-indigo-600 text-xs mt-3">
            <a href="{{ route('filament.admin.shop.resources.sales.orders') }}"
                class="ml-1 underline hover:text-indigo-700 focus:outline-none">
                Ke Halaman daftar pesanan
                <i class="fas fa-arrow-right"></i>
            </a>
        </p>
    </div>
</div>