@props(['record'=>[]])
<div class="flex justify-between items-start">
    <div>
        <h2 class="font-bold">Invoice</h2>
        <p class="text-gray-500"><span class="font-medium">{{$record->invoice}}</span></p>
    </div>
    <div>
        <h2 class="font-bold">Pelanggan</h2>
        <p class="text-gray-500"><span class="font-medium">{{$record->purchase->customer->name}}</span></p>
    </div>
    <div class="text-right">
        <p class="font-semibold">Kasir</p>
        <p class="text-gray-500">{{$record->purchase->user->name}}</p>
    </div>
</div>

{{-- Dates --}}
<div class="grid grid-cols-2 gap-4 mt-6">
    <div>
        <p class="text-gray-600">Transaksi Pembelian</p>
        {{-- <p class="font-medium text-gray-800">17 Mei 2025</p> --}}
    </div>
</div>

{{-- Items Table --}}
<table class="w-full text-sm">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="p-2 text-left">Item</th>
            <th class="p-2 text-center">Qty</th>
            <th class="p-2 text-center">Berat</th>
            <th class="p-2 text-center">Harga/<small>G</small></th>
            <th class="p-2 text-center">Karat Kadar</th>
            <th class="p-2 text-start">Harga</th>
        </tr>
    </thead>
    <tbody class="text-gray-800">
        @foreach ($record->purchase->purchaseDetails as $item)
        <tr class="border-b">
            <td class="p-2">{{$item->product->name}}</td>
            <td class="p-2 text-center">{{$item->quantity}}</td>
            <td class="p-2 text-center">{{$item->weight}}g</td>
            <td class="p-2 text-center">{{ number_format($item->product->karat->buy_price,
                0, ',', '.') }}</td>
            <td class="p-2 text-center">{{$item->product->karat->karat}} - {{$item->product->karat->rate}}%</td>
            <td class="p-2 ">{{ number_format($item->product->karat->buy_price*$item->weight*$item->quantity,
                0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="p-2  font-bold text-end">JUMLAH Rp.</td>
            <td style="background-color: #000000;" class=" px-1 font-bold text-white text-1xl">{{
                number_format($record->total_amount, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>