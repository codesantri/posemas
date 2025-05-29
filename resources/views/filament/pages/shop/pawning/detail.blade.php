
<div class="flex justify-between items-start">
    <div>
        <h2 class="font-bold">Invoice</h2>
        <p class="text-gray-500"><span class="font-medium">{{$record->invoice}}</span></p>
    </div>
    <div>
        <h2 class="font-bold">Pelanggan</h2>
        <p class="text-gray-500"><span class="font-medium">{{$record->pawning->customer->name}}</span></p>
    </div>
    <div class="text-right">
        <p class="font-semibold">Kasir</p>
        <p class="text-gray-500">{{$record->pawning->user->name}}</p>
    </div>
</div>

{{-- Dates --}}
<div class="grid grid-cols-2 gap-4 mt-6">
    <div>
        <p class="text-gray-600">Detail Penggadaian</p>
    </div>
</div>

{{-- Items Table --}}
<table class="w-full text-sm">
    <thead class=" ">
        <tr>
            <th class="p-2 text-left">Item</th>
            <th class="p-2 text-center">Kategori</th>
            <th class="p-2 text-center">Jenis</th>
            <th class="p-2 text-center">Kuantitas</th>
            <th class="p-2 text-center">Berat</th>
            <th class="p-2 text-center">Harga/<small>G</small></th>
            <th class="p-2 text-center">Karat Kadar</th>
        </tr>
    </thead>
    <tbody class="text-gray-800">
        @foreach ($record->pawning->details as $item)
        <tr class="border-b">
            <td class="p-2">
                @if ($item->image)
                <img alt="" class="w-24 h-24 rounded border object-cover flex-shrink-0" height="85"
                    src="{{ asset('storage/'.$item->image) }}" width="85" />
                @else
                <img alt="" class="w-24 h-24 rounded border object-cover flex-shrink-0" height="85" src="{{ asset('logo-cetak.png') }}"
                    width="85" />
                @endif
                {{$item->name}}
            </td>
            <td class="p-2 text-center">{{$item->category->name}}</td>
            <td class="p-2 text-center">{{$item->type->name}}</td>
            <td class="p-2 text-center">{{$item->quantity}}</td>
            <td class="p-2 text-center">{{$item->weight}}g</td>
            <td class="p-2 text-center">{{ number_format($item->karat->buy_price,
                0, ',', '.') }}</td>
            <td class="p-2 text-center">{{$item->karat->karat}} - {{$item->karat->rate}}%</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="grid grid-cols-2 gap-4 mt-6">
    <div>
        <p class="text-gray-600">Sistem secara otomatis melakukan perhitungan berdasarkan data yang telah dimasukkan.
        </p>
    </div>
</div>
<table class="w-full text-sm">
    <thead class=" ">
        <tr>
            <th class="p-2 text-left">Tanggal Penggadaian</th>
            <th class="p-2 text-left">Jatuh Tempo</th>
            <th class="p-2 text-center">Bunga</th>
            <th class="p-2 text-center">Status</th>
            <th class="p-2 text-center">Nilai Gadai</th>
        </tr>
    </thead>
    <tbody class="text-gray-800">
        <tr class="border-b">
            <td class="p-2">{{$record->pawning->pawn_date}}</td>
            <td class="p-2">{{$record->pawning->due_date}}</td>
            <td class="p-2 text-center">{{$record->pawning->rate}}%</td>
           <td class="p-2 text-center">
                @php
                $status = $record->pawning->status;
                $statusLabel = [
                'pending' => 'Menunggu Konfirmasi',
                'active' => 'Aktif',
                'paid_off' => 'Lunas',
                ][$status] ?? $status;
            
                $statusColor = [
                'pending' => 'warning',
                'active' => 'info',
                'paid_off' => 'success',
                ][$status] ?? 'gray';
                @endphp
            
                <x-filament::badge color="{{ $statusColor }}">
                    {{ $statusLabel }}
                </x-filament::badge>
            </td>
            <td class="p-2 text-center"><strong>Rp. {{ number_format($item->pawning->estimated_value,
            0, ',', '.') }}</strong></td>
        </tr>
        
    </tbody>
</table>