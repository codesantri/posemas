<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>
        Toko Mas Logam Mulia
    </title>
    <script src="https://cdn.tailwindcss.com">
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display&family=Roboto&display=swap"
        rel="stylesheet" />
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
            size: 255mm 140mm; /* Lebar x Tinggi */
            margin: 0; /* Optional, bisa diatur sesuai layout */
            padding: 0;
            }
        
        body {
        font-size: 12pt;
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

<body class="flex justify-center">
    {{-- Desain Nota --}}
    <div id="area" class="max-w-5xl w-full relative">
        <!-- Header -->
        <div class="flex justify-between items-center bg-[#000000] px-6 py-4">
            <div class="flex items-center space-x-3">
                <img alt="Gold colored logo icon of Toko Mas Logam Mulia" class="w-16 h-12 object-contain" 
                    src="{{ asset('logo-cetak.png') }}"
                    width="100" />
                <div>
                    <p class="text-[#C89A35] font-playfair text-2xl leading-none tracking-wide">
                        TOKO MAS
                    </p>
                    <p class="text-[#C89A35] font-playfair text-3xl leading-none tracking-wide -mt-1">
                        LOGAM MULIA
                    </p>
                </div>
            </div>
            <div class="flex items-end space-x-6 text-white text-sm font-light">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-map-marker-alt text-white">
                    </i>
                    <span>
                        Jl. Mesumai Pasar Bawah Bangko
                    </span>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <i class="fab fa-instagram text-white">
                        </i>
                        <span>
                            logammulia.bangko
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fab fa-whatsapp text-white">
                        </i>
                        <span>
                            0852-6666-9064 (Fajri)
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Form content -->
        <div class="flex border border-[#daa520] border-t-0">
            <!-- Vertical text left -->
            <div class="border-r  w-16 flex justify-center items-center bg-[#fff7e1]">
                <p class="text-[#daa520] text-lg font-bold" style="
                     writing-mode: vertical-rl;
                     text-orientation: mixed;
                     transform: rotate(180deg);
                     letter-spacing: 0.2rem;
                     font-family: 'Great Vibes', cursive;
                   ">
                    Berhias Sambil Menabung
                </p>
            </div>
            <!-- Main form -->
            <div class="flex-1 p-4">
                <div class="flex flex-wrap justify-between mb-2 text-sm font-normal">
                    <label class="mr-2">
                        Buat Bapak/Ibu
                    </label>
                    <div class="flex-1 border-b border-dotted border-black">{{ $invoice->customer->name }}</div>
                    <label class="ml-4 ">
                        Tinggal di
                    </label>
                    <div class="flex-1 border-b border-dotted border-black ml-2">{{ $invoice->customer->address }}</div>
                    <label class="ml-4 ">
                        Tipe
                    </label>
                    <div class="flex-1 border-b border-dotted border-black ml-2">Purchase</div>
                </div>
                <!-- Table -->
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#daa520] text-black font-semibold text-center">
                            <th class="border border-[#daa520] px-2 py-1 w-12">QTY</th>
                            <th class="border border-[#daa520] px-2 py-1">NAMA BARANG</th>
                            <th class="border border-[#daa520] px-2 py-1 w-12">BERAT</th>
                            <th class="border border-[#daa520] px-2 py-1 w-24">HARGA/ <small>G</small></th>
                            <th class="border border-[#daa520] px-2 py-1 w-48">SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $maxRows = 5;
                        $count = $invoice->purchaseDetails->count();
                        @endphp
                        
                        @foreach ($invoice->purchaseDetails as $item)
                        <tr class="bg-[#f7f5f3] text-center h-8">
                            <td class="border border-[#daa520] px-2 py-1">{{ $item->quantity }}</td>
                            <td class="border border-[#daa520] px-2 py-1">
                                <div class="flex justify-around items-center">
                                    {{ $item->product->name }}, {{ $item->product->karat->karat }}-{{ $item->product->karat->rate .'%' }}, {{ $item->weight }}
                                    <img src="https://api.thepalacejeweler.com/upload/article/1724140259-igs%206%20Agustus%20rev-04.jpg" alt="" srcset=""
                                                                        width="150">
                                </div>
                            </td>
                            <td class="border border-[#daa520] px-2 py-1">{{ $item->weight }}</td>
                            <td class="border border-[#daa520] px-2 py-1">{{ number_format($item->product->karat->buy_price, 0, ',', '.') }}</td>
                            <td class="border border-[#daa520] px-2 py-1">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        
                        {{-- Tambahin baris kosong kalau kurang dari 5 --}}
                        {{-- @for ($i = 0; $i < $maxRows - $count; $i++) <tr class="bg-[#f7f5f3] text-center h-8">
                            <td class="border border-[#daa520] px-2 py-1">&nbsp;</td>
                            <td class="border border-[#daa520] px-2 py-1"></td>
                            <td class="border border-[#daa520] px-2 py-1"></td>
                            <td class="border border-[#daa520] px-2 py-1"></td>
                            <td class="border border-[#daa520] px-2 py-1"></td>
                            </tr>
                            @endfor --}}
                    </tbody>
                    <tfoot>
                        @php
                        use Carbon\Carbon;
                        $tanggal = Carbon::now()->format('d');
                        $bulan = Carbon::now()->locale('id')->isoFormat('MMMM');
                        $tahun = Carbon::now()->format('Y');
                        @endphp
                        <tr>
                            <td class="border-t border-[#daa520] px-2 py-2 text-left font-normal text-sm" colspan="2">
                                Bangko, {{ \Carbon\Carbon::parse($invoice->created_at)->locale('id')->isoFormat('D MMMM Y') }}
                            </td>
                            <td class="border-t border-[#daa520] px-2 py-2 text-right font-semibold" colspan="2">
                                JUMLAH Rp.
                            </td>
                            <td class="bg-[#b39750] border border-[#daa520] px-2 py-1 font-semibold text-left">
                                {{ number_format($invoice->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <!-- Date and place -->
                <div class="flex items-start text-sm font-normal w-full space-x-4">
                    {{-- Logo --}}
                    <div class="border border-[#daa520] p-3">
                        <img src="{{ asset('logo-cetak.png') }}" alt="logo-cetak.png" class="opacity-25" width="120">
                    </div>
                
                    {{-- Terbilang + Baris kosong --}}
                    <div class="space-y-2 w-full">
                        {{-- Baris pertama: tulisan terbilang --}}
                        {{-- <div class="relative w-full h-8 border-b border-[#daa520]">
                            <span class="absolute inset-0 flex items-center justify-center text-center bg-white px-2 text-sm">
                                Satu Juta Rupiah
                            </span>
                        </div> --}}
                    
                        {{-- Baris kosong --}}
                        {{-- @for ($i = 0; $i < 3; $i++) 
                        <div class="border-b border-[#daa520] w-full">
                        </div>
                        @endfor --}}
                    <input type="text" value="{{ spelledOut($invoice->total_amount) }}" disabled
                        class="w-full px-4 py-2 border-0 h-10 mt-3 text-2xl  bg-[#fff7e1]   text-gray-500 cursor-not-allowed focus:outline-none focus:ring-0"
                        style="clip-path: polygon(20px 0%, calc(100% - 10px) 0%, 100% 100%, 0% 100%); border: 1px solid #e1d5b5; border-radius: 0; font-style: italic;" />
                        <small>NB : Barang yang tersebut di atas boleh diterima kembali menurut harga pasaran waktu menjual dipotong ongkos pembuatan.</small>
                        <div class="mt-0 flex items-end justify-between">
                            <div>
                                <p style="font-size: 12px" class="font-bold">Terima Kasih,</p>
                                <p style="font-size: 12px" class="font-bold">Semoga Tetap Menjadi Langganan Kami</p>
                            </div>

                            <!-- Logo Bank -->
                            <div class="flex items-end space-x-2 justify-end">
                                <img alt="BSI Bank Syariah Indonesia logo" class="h-5 object-contain" src="https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia.svg" width="80" />
                                <img alt="Bank Mandiri logo" class="h-5 object-contain" src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg" width="80" />
                                <img alt="BCA bank logo" class="h-5 object-contain" src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" width="80" />
                                <img alt="BNI bank logo" class="h-5 object-contain" src="https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg" width="80" />
                                <img alt="BRI bank logo" class="h-5 object-contain" src="https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_2020.svg" width="80" />
                            </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Desain Nota --}}
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