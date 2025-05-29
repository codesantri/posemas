{{-- <x-layout-print
:customer="$invoice->sale->customer->name"
:address="$invoice->sale->customer->address"
:created="$invoice->sale->transaction_date"
:total="$invoice->sale->total_amount"
:chasier="$invoice->sale->user->name"
:type="'Sale'"
>
    <tbody>
        @php
        $maxRows = 5;
        $count = $invoice->sale->saleDetails->count();
        @endphp
    
        @foreach ($invoice->sale->saleDetails as $item)
        <tr class="bg-[#f7f5f300] text-center h-6">
            <td class="border border-[#daa52000] p-0 text-start py-0">{{ $item->quantity }}</td>
            <td class="border border-[#daa52000] p-0 text-start py-0">
                <div class="flex justify-around items-center">
                    {{ $item->product->name }}, {{ $item->product->karat->karat }}-{{
                    $item->product->karat->rate .'%' }}, {{ $item->weight }}
                    <img src="https://api.thepalacejeweler.com/upload/article/1724140259-igs%206%20Agustus%20rev-04.jpg"
                        alt="" srcset="" width="150">
                </div>
            </td>
            <td class="border border-[#daa52000] p-0 text-start py-0">{{ $item->weight }}</td>
            <td class="border border-[#daa52000] p-0 text-start py-0">{{
                number_format($item->product->karat->buy_price, 0, ',', '.') }}</td>
            <td class="border border-[#daa52000] p-0 text-start py-0">{{ number_format($item->subtotal, 0, ',', '.')
                }}</td>
        </tr>
        @endforeach
    
        Tambahin baris kosong kalau kurang dari 5
        @for ($i = 0; $i < $maxRows - $count; $i++) <tr class="bg-[#f7f5f300] text-center h-6">
            <td class="border border-[#daa52000] p-0 text-start py-0">&nbsp;</td>
            <td class="border border-[#daa52000] p-0 text-start py-0"></td>
            <td class="border border-[#daa52000] p-0 text-start py-0"></td>
            <td class="border border-[#daa52000] p-0 text-start py-0"></td>
            <td class="border border-[#daa52000] p-0 text-start py-0"></td>
            </tr>
            @endfor
    </tbody>
</x-layout-print> --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Document</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
    
        .vertical-text {
            writing-mode: vertical-rl;
            text-orientation: mixed;
        }
    
        @media print {
            @page {
                size: 21.6cm 11cm;
                /* Lebar x Tinggi */
                margin: 0;
                /* Optional, bisa diatur sesuai layout */
                padding: 0;
            }
    
            body {
                font-size: 12pt ;
                color: black;
                padding: 0;
                margin: 0;
            }
    
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="container mb-4" style="margin-top: 3.9cm; margin-left: 8.2cm;">
        <div class="row ">
            <div class="col">
                <span class="ms-4">{{ $invoice->sale->customer->name }}</span>
            </div>
            <div class="col ">
                <span class="ms-5">{{ $invoice->sale->customer->address }}</span>
            </div>
        </div>
    </div>

    <table class="table" style="margin-left: 5cm; margin-top:2.3rem">
        <tbody >
            @php
            $maxRows = 5;
            $count = $invoice->sale->saleDetails->count();
            @endphp

            @foreach ($invoice->sale->saleDetails as $item)
            <tr>
                <td class="text-center border-0 py-1" >{{ $item->quantity }}</td>
                <td style="width: 40% " class="border-0 py-1" >{{ $item->product->name }}</td>
                <td class="border-0 py-1" style="text-align: start !important;">Rp.{{ number_format($item->product->karat->buy_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @for ($i = 0; $i < $maxRows - $count; $i++) <tr>
                    <td class="text-center border-0 py-1 text-white">0</td>
                    <td style="width: 40% " class="border-0 py-1 text-white">Nama</td>
                    <td class="border-0 py-1 text-white" style="text-align: start !important;">Rp.0000m0</td>
                </tr>
            @endfor
        </tbody>
        {{-- <tfoot class="border-0">
            <tr>
                <td class="border-0" colspan="1">
                    Bangko, {{ \Carbon\Carbon::parse($invoice->created)->locale('id')->isoFormat('D MMMM
                    Y') }}
                </td>
                <td class="border-0">
                    {{ number_format($invoice->sale->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot> --}}
    </table>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js">
    </script>
    <script>
        window.addEventListener('load', function () {
            window.print();
            window.onafterprint = function () {
              history.back();
            };
          });
    </script>
</body>
</html>