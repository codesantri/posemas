@props(['record'=>[]])
<div class="flex justify-between items-start">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Invoice</h2>
        <p class="text-gray-500"><span class="font-medium">{{$record->invoice}}</span></p>
    </div>
    <div class="text-right">
        <p class="font-semibold text-lg text-gray-800">Kasir</p>
        <p class="text-gray-500">{{$record->user->name}}</p>
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
            <th class="p-2 text-right">Subtotal</th>
        </tr>
    </thead>
    <tbody class="text-gray-800">
        @foreach ($record->purchaseDetails as $item)
        <tr class="border-b">
            <td class="p-2">{{$item->product->name}}</td>
            <td class="p-2 text-center">{{$item->quantity}}</td>
            <td class="p-2 text-center">{{$item->weight}}g</td>
            <td class="p-2 text-center">{{ number_format($item->product->karat->buy_price,
            0, ',', '.') }}</td>
            <td class="p-2 text-right">{{ number_format($item->product->karat->buy_price*$item->weight*$item->quantity,
                0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #ef7a14;">
            <td colspan="4" class="p-2  font-bold text-white">Total</td>
            <td class="p-2 text-right font-bold text-white text-2xl">Rp {{
                number_format($record->total_amount, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>