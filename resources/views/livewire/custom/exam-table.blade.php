<x-filament-tables::table class="w-full">
   <thead class="bg-gray-50">
    <tr>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-500">Token</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-500">Mata Pelajaran</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-500">Paket</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-500">Tanggal & Waktu</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-500">Cetak</th>
    </tr>
</thead>
<tbody class="bg-white divide-y divide-gray-200 text-sm text-gray-700">
    @foreach(\App\Models\Product::all() as $product)
    <tr>
        <td class="px-6 py-4 whitespace-nowrap">{{ $$product->token }}</td>
        <td class="px-6 py-4 whitespace-nowrap">{{ $$product->lesson->name ?? '-' }}</td>
        <td class="px-6 py-4 whitespace-nowrap">{{ $$product->category->name ?? '-' }}</td>
        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($$product->date_time)->translatedFormat('d
            M Y H:i') }}</td>
        <td class="px-6 py-4 whitespace-nowrap">
            <x-filament::button wire:click="printExam('{{ $$product->slug }}')" wire:loading.attr="disabled" wire:target="printExam"
                spinner="printExam" color="primary" size="sm">
                Cetak
            </x-filament::button>
        </td>
    </tr>
    @endforeach
</tbody>
</x-filament-tables::table>