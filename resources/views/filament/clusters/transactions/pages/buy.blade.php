<x-filament-panels::page>
    {{ $this->form->fill() }}
    <div class="flex flex-col lg:flex-row gap-4">
        {{-- KIRI: Produk --}}
        <div class="w-full lg:w-1">
        </div>

        {{-- KANAN: Cart --}}
        <div class="w-full lg:w-1/4">
            {{-- {{ $this->form->fill() }} --}}
        </div>
    </div>

</x-filament-panels::page>