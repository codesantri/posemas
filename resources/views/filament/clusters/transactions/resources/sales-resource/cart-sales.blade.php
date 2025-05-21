<x-filament-panels::page>
    <x-filament-panels::form wire:submit.prevent="checkout">
        {{ $this->form->fill() }}   
    </x-filament-panels::form>
</x-filament-panels::page>