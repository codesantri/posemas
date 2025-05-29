<x-layout-print :customer="$invoice->pawning->customer->name" :address="$invoice->pawning->customer->address"
    :created="$invoice->pawning->transaction->transaction_date" :total="$invoice->pawning->transaction->total_amount"
    type="Gadai">
    <tbody>
        @php
        $maxRows = 5;
        $details = $invoice->pawning->details;
        $count = $details->count();
        @endphp

        @foreach ($details as $item)
        <tr class="bg-[#f7f5f3] text-center text-sm h-8">
            <td class="border border-[#daa520] px-2 py-1">{{ $item->quantity }}</td>
            <td class="border border-[#daa520] px-2 py-1">
                {{ $item->name }}, {{ $item->karat->karat }}K ({{ $item->karat->rate }}%), {{ $item->weight }} gr
            </td>
            <td class="border border-[#daa520] px-2 py-1">{{ $item->weight }}</td>
            <td class="border border-[#daa520] px-2 py-1">{{ number_format($item->karat->buy_price, 0, ',', '.') }}</td>
            <td class="border border-[#daa520] px-2 py-1">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach

        @for ($i = 0; $i < $maxRows - $count; $i++) <tr class="bg-[#f7f5f3] text-center text-sm h-8">
            <td class="border border-[#daa520] px-2 py-1">&nbsp;</td>
            <td class="border border-[#daa520] px-2 py-1"></td>
            <td class="border border-[#daa520] px-2 py-1"></td>
            <td class="border border-[#daa520] px-2 py-1"></td>
            <td class="border border-[#daa520] px-2 py-1"></td>
            </tr>
            @endfor
    </tbody>

    <tfoot>
        <tr>
            <td colspan="4" class="text-right border-t border-[#daa520] px-4 py-2 font-bold">Total</td>
            <td class="text-center border-[#daa520] px-4 py-2 font-bold">
                {{ number_format($invoice->pawning->transaction->total_amount, 0, ',', '.') }}
            </td>
        </tr>
    </tfoot>
</x-layout-print>