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


<div class="grid grid-cols-2 gap-4 mt-6">
    <div>
        <p class="">Kalkulasi otomasti oleh Sistem</p>
    </div>
</div>

<table class="w-full text-sm">
    <thead class="">
        <tr>
            <th class="p-2 text-left">Tanggal Gadai</th>
            <th class="p-2 text-left">Jatuh Tempo</th>
            <th class="p-2 text-center">Bunga</th>
            <th class="p-2 text-center">Nilai Gadai</th>
        </tr>
    </thead>
    <tbody class="text-gray-800">
        <tr class="border-b">
            <td class="p-2">{{$record->pawning->pawn_date}}</td>
            <td class="p-2">{{$record->pawning->due_date}}</td>
            <td class="p-2 text-center">{{$record->pawning->rate}}%</td>
            <td class="p-2 text-center"><strong>Rp. {{ number_format($record->pawning->estimated_value,
                    0, ',', '.') }}</strong></td>
        </tr>

    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="p-2  font-bold text-end">JUMLAH Rp.</td>
            <td style="background-color: #000000;" class=" px-1 font-bold text-white text-1xl">{{
                number_format($total, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>