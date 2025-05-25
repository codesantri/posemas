<x-filament-panels::page>
<x-filament-panels::form wire:submit="confirmationPawning">
    {{ $this->form->fill() }}
    {{-- @foreach ($this->getCachedActions() as $action)
    {{ $action }}
    @endforeach --}}
</x-filament-panels::form>
</x-filament-panels::page>
