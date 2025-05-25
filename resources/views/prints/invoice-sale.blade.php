<x-layout-print
:customer="$invoice->sale->customer->name"
:address="$invoice->sale->customer->address"
:created="$invoice->sale->transaction_date"
:total="$invoice->sale->total_amount"
:type="'Sale'"
>
    <tbody>
        @php
        $maxRows = 5;
        $count = $invoice->sale->saleDetails->count();
        @endphp
    
        @foreach ($invoice->sale->saleDetails as $item)
        <tr class="bg-[#f7f5f3] text-center h-8">
            <td class="border border-[#daa520] px-2 py-1">{{ $item->quantity }}</td>
            <td class="border border-[#daa520] px-2 py-1">
                <div class="flex justify-around items-center">
                    {{ $item->product->name }}, {{ $item->product->karat->karat }}-{{
                    $item->product->karat->rate .'%' }}, {{ $item->weight }}
                    {{-- <img src="https://api.thepalacejeweler.com/upload/article/1724140259-igs%206%20Agustus%20rev-04.jpg"
                        alt="" srcset="" width="150"> --}}
                </div>
            </td>
            <td class="border border-[#daa520] px-2 py-1">{{ $item->weight }}</td>
            <td class="border border-[#daa520] px-2 py-1">{{
                number_format($item->product->karat->buy_price, 0, ',', '.') }}</td>
            <td class="border border-[#daa520] px-2 py-1">{{ number_format($item->subtotal, 0, ',', '.')
                }}</td>
        </tr>
        @endforeach
    
        {{-- Tambahin baris kosong kalau kurang dari 5 --}}
        @for ($i = 0; $i < $maxRows - $count; $i++) <tr class="bg-[#f7f5f3] text-center h-8">
            <td class="border border-[#daa520] px-2 py-1">&nbsp;</td>
            <td class="border border-[#daa520] px-2 py-1"></td>
            <td class="border border-[#daa520] px-2 py-1"></td>
            <td class="border border-[#daa520] px-2 py-1"></td>
            <td class="border border-[#daa520] px-2 py-1"></td>
            </tr>
            @endfor
    </tbody>
</x-layout-print>