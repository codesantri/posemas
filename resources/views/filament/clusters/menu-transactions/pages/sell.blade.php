<x-filament-panels::page>
        {{-- <x-filament-tables::search-field/> --}}
<div class="flex flex-col lg:flex-row gap-4">
    {{-- KIRI: Produk --}}
    <div class="w-full">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center lg:justify-between gap-4">
            <x-filament-panels::global-search.field wire:model.live="search" autofocus />
            
            <x-filament::dropdown>
                <x-slot name="trigger">
                    <x-filament::button>
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="w-5 h-5 text-white mr-2" />
                    </x-filament::button>
                </x-slot>
            
                <x-filament::dropdown.list>
                    {{-- Semua Kategori --}}
                    <x-filament::dropdown.list.item wire:click="$set('categoryId', '')"
                        style="{{ $categoryId === '' || $categoryId === null ? 'background-color: #ea9101; color: white;' : '' }}">
                        Semua Kategori
                    </x-filament::dropdown.list.item>
            
                    {{-- Looping kategori --}}
                    @foreach (\App\Models\Category::orderBy('name')->get() as $category)
                    <x-filament::dropdown.list.item wire:click="$set('categoryId', {{ $category->id }})"
                        style="{{ $categoryId == $category->id ? 'background-color: #ea9101; color: white;' : '' }}">
                        {{ $category->name }}
                    </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
            @forelse ($this->products as $item)
                <div class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <img class="p-8 rounded-t-lg" src="{{ asset('emas.jpg') }}" alt="product image" />
                    <div class="px-3 py-3">
                        <div class="flex items-center justify-around my-2">
                            <x-filament::badge color="info" class="mx-1">
                                {{ $item->karat->karat }} {{ $item->karat->rate. ' %' }}
                            </x-filament::badge>
                            <x-filament::badge color="success">
                                    Stok {{ $item->stockTotals->total ?? 0 }}
                            </x-filament::badge>
                        </div>
                        <h5 class="text-lg tracking-tight my-2 text-gray-900 dark:text-white">
                            {{ $item->name }}
                            <div class="text-start flex justify-start">
                                <span class="text-xl font-semibold dark:text-white" style="color: #f5b400">
                                    Rp {{ number_format($item->karat->sell_price * $item->weight, 0, ',', '.') }}
                                </span>
                            </div>
                        </h5>
                
                        <div class="flex items-center justify-between">
                            <x-filament::button wire:click='addToCart({{ $item->id }})' color="success" class="w-full text-xs flex items-center justify-center gap-1">
                                <x-filament::icon icon="heroicon-o-shopping-cart" class="w-4 h-4 inline-block" />
                                <span>Add To Cart</span>
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @empty
                
            @endforelse
        </div>
        <div class="my-4">
            <x-filament::pagination :paginator="$this->products"/>
        </div>
    </div>

    {{-- KANAN: Cart --}}
    {{-- <div class="w-full lg:w-1/4">
        {{ $this->form->fill() }}
        <x-filament::card>
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
        </x-filament::card>
    </div> --}}
</div>

</x-filament-panels::page>
