<div class="flex justify-between items-start">
    <div>
        <h2 class="font-bold">Invoice</h2>
        <p class="text-gray-500"><span class="font-medium">{{$record->invoice}}</span></p>
    </div>
    <div>
        <h2 class="font-bold">Pelanggan</h2>
        <p class="text-gray-500"><span class="font-medium">{{$record->sale->customer->name}}</span></p>
    </div>
    <div class="text-right">
        <p class="font-semibold">Kasir</p>
        <p class="text-gray-500">{{$record->sale->user->name}}</p>
    </div>
</div>


<div class="grid grid-cols-2 gap-4 mt-6">
    <div>
        <p class="text-gray-600">Jumlah Pembayaran</p>

        @php
        $old = $record->purchase->total_amount ?? 0;
        $new = $record->sale->total_amount ?? 0;
        $difference = $new - $old;
        @endphp

        <h1 class="text-2xl font-semibold {{ $difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $difference >= 0 ? 'Rp ' : '-Rp ' }}
            {{ number_format(abs($difference), 0, ',', '.') }}
        </h1>

        <p class="text-xs text-gray-400 mt-1">
            {{ $difference >= 0 ? 'Jumlah yang harus dibayar pelanggan' : 'Jumlah yang dikembalikan ke pelanggan' }}
        </p>
    </div>
</div>