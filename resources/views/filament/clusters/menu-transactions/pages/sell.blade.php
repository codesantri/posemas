<x-filament-panels::page>
        {{-- <x-filament-tables::search-field/> --}}
<div class="flex flex-col lg:flex-row gap-4">
    {{-- KIRI: Produk --}}
    <div class="w-full lg:w-3/4">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
            @foreach(range(1, 6) as $index)
                <div class="p-2 shadow-lg rounded flex flex-col items-center justify-center text-center space-y-1">
                    <h6 class="text-[10px] font-semibold">Emas {{ $index }} gr</h6>
                    <img src="{{ asset('logo.png') }}" alt="Emas {{ $index }}" class="w-20 h-20 object-contain" />
                    <p class="text-[11px] text-center my-2 text-green-600 font-bold">Rp {{ number_format(10_000_000, 0, ',', '.') }}</p>
                    <x-filament::button color="success" class="w-full text-[10px] flex items-center justify-center gap-1">
                        <x-filament::icon icon="heroicon-o-shopping-cart" class="w-4 h-4 inline-block" />
                        <span>Add To Cart</span>
                    </x-filament::button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- KANAN: Cart --}}
    <div class="w-full lg:w-1/4">
        {{ $this->form->fill() }}
        {{-- <x-filament::card>
            <h2 class="text-sm font-bold mb-2">Keranjang</h2>
            @foreach(range(1, 3) as $index)
                <div class="border-b py-2 text-sm flex justify-between items-center">
                    <span>Emas {{ $index }} gr</span>
                    <span class="font-bold text-green-600">Rp {{ number_format(10_000_000, 0, ',', '.') }}</span>
                </div>
            @endforeach
            <div class="mt-4 border-t pt-2 text-right">
                <p class="text-sm">Total: <span class="font-bold text-green-700">Rp 30.000.000</span></p>
                <x-filament::button color="primary" class="mt-2 w-full">
                    Bayar
                </x-filament::button>
            </div>
        </x-filament::card> --}}
    </div>
</div>

</x-filament-panels::page>
