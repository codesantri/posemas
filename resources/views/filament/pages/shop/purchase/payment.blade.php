<x-filament-panels::page>
    <x-filament-panels::form wire:submit="processPayment">
        {{ $this->form->fill() }}
    </x-filament-panels::form>
</x-filament-panels::page>