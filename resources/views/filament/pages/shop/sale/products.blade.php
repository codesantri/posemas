<x-filament::page>
    <div class="flex justify-between items-center mb-4">
        <x-filament-panels::global-search.field wire:model.debounce.300ms="search" autofocus />
    
        <div class="flex items-center gap-2 mx-3">
            <x-filament::dropdown>
                <x-slot name="trigger">
                    <x-filament::button>
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="w-5 h-5 text-white mx-1 inline-block" />
                    </x-filament::button>
                </x-slot>

                <x-filament::dropdown.list>
                    <x-filament::dropdown.list.item wire:click="$set('categoryId', '')"
                        :class="$categoryId === '' || $categoryId === null ? 'bg-warning-600 text-white' : ''">
                        Semua Kategori
                    </x-filament::dropdown.list.item>

                    @foreach (\App\Models\Category::orderBy('name')->get() as $category)
                    <x-filament::dropdown.list.item wire:click="$set('categoryId', {{ $category->id }})"
                        :class="$categoryId == $category->id ? 'bg-warning-600 text-white' : ''">
                        {{ $category->name }}
                    </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>

            <!-- KERANJANG -->
            <x-filament::button color="success" class="relative mx-1" wire:click='gotoCart'>
                <x-filament::icon icon="heroicon-o-shopping-bag" class="w-5 h-5 inline-block" />
                <x-slot name="badge" color="success">
                    {{ $totalOrder }}
                </x-slot>
            </x-filament::button>
            <x-filament::button color="info" class="relative" wire:click='gotoOrder'>
                <x-filament::icon icon="heroicon-o-shopping-cart" class="w-5 h-5 inline-block" />
                <x-slot name="badge" color="success">
                    {{ $totalCheckout }}
                </x-slot>
            </x-filament::button>
        </div>
    </div>

    <!-- PRODUK GRID -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
        @forelse ($this->products as $item)
        <div
            class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <img class="p-8 rounded-t-lg" src="{{ asset('emas.jpg') }}" alt="product image" />
            <div class="px-3 py-3">
                <div class="flex items-center justify-around my-2">
                    <x-filament::badge color="info" class="mx-1">
                        {{ $item->karat->karat }} {{ $item->karat->rate . ' %' }}
                    </x-filament::badge>
                    <x-filament::badge color="success">
                        Stok {{ $item->stockTotals->total ?? 0 }}
                    </x-filament::badge>
                </div>
                <h5 class="text-lg tracking-tight my-2 text-gray-900 dark:text-white">
                    {{ $item->name }}
                </h5>
                <div class="text-start">
                    <span class="text-xl font-semibold" style="color: #f5b400">
                        Rp {{ number_format($item->karat->sell_price * $item->weight, 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-3">
                    <x-filament::button wire:click="addToCart({{ $item->id }})" color="success"
                        class="w-full text-xs flex items-center justify-center gap-1">
                        <x-filament::icon icon="heroicon-o-shopping-cart" class="w-4 h-4 inline-block" />
                        <span>Add To Cart</span>
                    </x-filament::button>
                </div>
            </div>
        </div>
        @empty
        <p class="col-span-full text-center text-gray-500">Tidak ada produk tersedia.</p>
        @endforelse
    </div>
        <x-filament::pagination :paginator="$this->products" />
</x-filament::page>