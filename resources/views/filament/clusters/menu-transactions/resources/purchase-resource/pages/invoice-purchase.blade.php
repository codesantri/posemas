
<x-filament-panels::page>
<x-filament-panels::form wire:submit="processPayment">
    <x-filament::grid default="2" sm="2" md="2" xl="2" class="gap-6">
        {{-- Kartu Kiri --}}
        <x-filament::card>
            <h2 class="text-lg font-bold">Proses Pembayaran</h2>
            <p class="text-sm text-gray-600 mb-5">Isi sesuai kebutuhan, bisa info customer, status pembayaran, dsb.</p>
            {{ $this->form->fill() }}
            <x-filament::button type="submit" color="success"
                class="w-full text-[10px] flex items-center justify-center gap-1 my-4">
                <x-filament::icon icon="heroicon-o-credit-card" class="w-4 h-4 inline-block" />
                <span>Proses Pembayaran</span>
            </x-filament::button>
        </x-filament::card>

        {{-- Kartu Kanan (Invoice) --}}
        <x-filament::card>
            {{-- Header --}}
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
            <div class="overflow-x-auto mt-6">
                <table class="w-full border-collapse border border-gray-200 text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border p-2 text-left">Item</th>
                            <th class="border p-2 text-right">Qty</th>
                            <th class="border p-2 text-right">Berat</th
                            <th class="border p-2 text-right">Harga</th>
                            <th class="border p-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-800">
                        @foreach ($record->purchaseDetails as $item)
                        <tr>
                            <td class="border p-2">{{$item->product->name}}</td>
                            <td class="border p-2 text-right">{{$item->quantity}}</td>
                            <td class="border p-2 text-right">{{$item->weight}}g</td>
                            <td class="border p-2 text-right">Rp {{ number_format($item->product->karat->buy_price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 text-gray-700">
                        <tr>
                            <td colspan="3" class="border p-2 text-right font-semibold">Total</td>
                            <td class="border p-2 text-right font-bold text-gray-900">Rp {{ number_format($record->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Footer Note --}}
            <div class="text-xs text-gray-500 pt-6">
                <p>Terima kasih telah melakukan transaksi dengan kami 🙏</p>
                <p>Invoice ini sah tanpa tanda tangan atau stempel.</p>
            </div>
        </x-filament::card>
    </x-filament::grid>
</x-filament-panels::form>
</x-filament-panels::page>