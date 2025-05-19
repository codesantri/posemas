<?php

namespace App\Filament\Clusters\MenuTransactions\Resources\PurchaseResource\Pages;

use Filament\Actions;
use App\Models\Purchase;
use Filament\Forms\Form;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Clusters\MenuTransactions\Resources\PurchaseResource;

class InvoicePurchase extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = PurchaseResource::class;

    protected static string $view = 'filament.clusters.menu-transactions.resources.purchase-resource.pages.invoice-purchase';
    protected static ?string $title = 'Invoice';
    protected static ?string $breadcrumb = 'Invoice';

    public Purchase $record;
    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function mount(string $invoice): void
    {
        $this->record = Purchase::where('invoice', $invoice)->firstOrFail();
        $this->redirectBack($this->record);
        $this->record->status = 'success';
        $this->form->fill();
    }

    public function redirectBack($record)
    {
        if ($record->status == 'success') {
            redirect()->route('filament.admin.menu-transactions.resources.purchases.index');
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }


    public function getFormSchema(): array
    {
        return [
            TextInput::make('cash')
                ->label('Pembayaran')
                ->prefix('Rp.')
                ->placeholder('Masukkan nominal')
                ->inputMode('decimal')
                ->extraAttributes([
                    'x-data' => '{}',
                    'x-init' => <<<JS
            \$el.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\\d]/g, '');
                value = new Intl.NumberFormat('id-ID').format(value);
                e.target.value = value;
            });
        JS,
                    'class' => 'py-2 w-full rounded-lg',
                    'style' => 'font-size: 3rem; font-weight: bold;',
                ]),


            Radio::make('payment_method')
                ->label('Metode Pemabayaran')
                ->options([
                    'cash' => 'Tunai',
                    'online' => 'Tranfers'
                ])->default('cash')->inline(),
        ];
    }


    public function processPayment()
    {
        $cash = (int) str_replace('.', '', $this->data['cash']);
        $total = $this->record->total_amount;
        $paymentMethod = $this->data['payment_method'];
        if ($cash == null) {
            Notification::make()
                ->title('Silakan masukkan jumlah pembayaran')
                ->danger()
                ->send();
            return;
        }
        // Validasi: jumlah pembayaran harus pas
        if ($cash !== $total) {
            Notification::make()
                ->title('Jumlah pembayaran tidak sesuai')
                ->body("Jumlah uang harus pas: Rp" . number_format($total, 0, ',', '.'))
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        // Set metode pembayaran & status
        $this->record->payment_method = $paymentMethod;
        $this->record->status = $paymentMethod === 'cash' ? 'success' : 'pending';
        $this->record->save();

        // Notifikasi sukses
        Notification::make()
            ->title('Pembayaran berhasil')
            ->body("Pembayaran berhasil untuk invoice #{$this->record->invoice}")
            ->success()
            ->send();
        // Redirect ke halaman transaksi
        return redirect()->route('print.purchase', $this->record->invoice);
    }
}
