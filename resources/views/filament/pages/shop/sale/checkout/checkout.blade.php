<x-filament-panels::page>
    <x-filament-panels::form wire:submit.prevent="paymentProcess">
        {{ $this->form->fill() }}  
    </x-filament-panels::form>
</x-filament-panels::page>