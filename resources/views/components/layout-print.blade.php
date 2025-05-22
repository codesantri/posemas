@extends('components.layouts.app')
@section('content')
@props([
    'customer'=>'',
    'address'=>'',
    'type'=>'',
    'total'=>0,
    'created'=>''
])
<div class="flex-1 p-4">
    <div class="flex flex-wrap justify-between mb-2 text-sm font-normal">
        <label class="mr-2">
            Buat Bapak/Ibu
        </label>
        <div class="flex-1 border-b border-dotted border-black">{{ $customer }}</div>
        <label class="ml-4 ">
            Tinggal di
        </label>
        <div class="flex-1 border-b border-dotted border-black ml-2">{{ $address }}</div>
        <label class="ml-4 ">
            Tipe
        </label>
        <div class="flex-1 border-b border-dotted border-black ml-2">{{$type}}</div>
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
            {{ $slot }}
        <tfoot>
            <tr>
                <td class="border-t border-[#daa520] px-2 py-2 text-left font-normal text-sm" colspan="2">
                    Bangko, {{ \Carbon\Carbon::parse($created)->locale('id')->isoFormat('D MMMM
                    Y') }}
                </td>
                <td class="border-t border-[#daa520] px-2 py-2 text-right font-semibold" colspan="2">
                    JUMLAH Rp.
                </td>
                <td class="bg-[#b39750] border border-[#daa520] px-2 py-1 font-semibold text-left">
                    {{ number_format($total, 0, ',', '.') }}
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
            {{-- @for ($i = 0; $i < 3; $i++) <div class="border-b border-[#daa520] w-full">
        </div>
        @endfor --}}
        <input type="text" value="{{ spelledOut($total) }}" disabled
            class="w-full px-4 py-2 border-0 h-10 mt-3   bg-[#fff7e1]   text-gray-500 cursor-not-allowed focus:outline-none focus:ring-0"
            style="clip-path: polygon(20px 0%, calc(100% - 10px) 0%, 100% 100%, 0% 100%); border: 1px solid #e1d5b5; border-radius: 0; font-style: italic;" />
        <small>NB : Barang yang tersebut di atas boleh diterima kembali menurut harga pasaran waktu menjual
            dipotong ongkos pembuatan.</small>
        <div class="mt-0 flex items-end justify-between">
            <div>
                <p style="font-size: 12px" class="font-bold">Terima Kasih,</p>
                <p style="font-size: 12px" class="font-bold">Semoga Tetap Menjadi Langganan Kami</p>
            </div>

            <!-- Logo Bank -->
            <div class="flex items-end space-x-2 justify-end">
                <img alt="BSI Bank Syariah Indonesia logo" class="h-5 object-contain"
                    src="https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia.svg" width="80" />
                <img alt="Bank Mandiri logo" class="h-5 object-contain"
                    src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg" width="80" />
                <img alt="BCA bank logo" class="h-5 object-contain"
                    src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" width="80" />
                <img alt="BNI bank logo" class="h-5 object-contain"
                    src="https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg" width="80" />
                <img alt="BRI bank logo" class="h-5 object-contain"
                    src="https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_2020.svg" width="80" />
            </div>
        </div>
    </div>
</div>
@endsection