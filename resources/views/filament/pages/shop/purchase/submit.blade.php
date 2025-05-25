<x-filament::button :type="$type ?? 'submit'" class="w-full text-[10px] flex items-center justify-center gap-1 my-4">
    <x-filament::icon icon="heroicon-o-credit-card" class="w-4 h-4 inline-block" />
    <span>{{ $label ?? 'Submit' }}</span>
</x-filament::button>