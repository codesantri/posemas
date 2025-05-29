<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeResource\Pages;

use Carbon\Carbon;
use App\Models\Stock;
use Filament\Forms\Form;
use App\Models\StockTotal;
use App\Models\Transaction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Clusters\Shop\Resources\ChangeResource;
use Filament\Forms\Components\Actions\Action as FormAction;

class PaymentChangePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ChangeResource::class;
    protected static string $view = 'filament.pages.shop.change.change-payment';

    public Transaction $transaction;
    public ?string $typeChange = '';
    public array $data = [];
    public float|int $cash = 0;
    public float|int $change = 0;
    public float|int $total = 0;

    protected $queryString = ['payment_method'];
    protected $listeners = ['updatedCash'];

    public function mount(string $invoice): void
    {
        if (empty($invoice)) {
            $this->redirect(ChangeResource::getUrl('index'));
            return;
        }


        $this->loadPage($invoice);
        $this->form->fill();
    }

    public function loadPage(string $invoice): void
    {
        $this->transaction = Transaction::where('invoice', $invoice)->firstOrFail();

        if ($this->transaction->status === 'success') {
            $this->redirect(ChangeResource::getUrl('index'));
            return;
        }

        $this->data['payment_method'] = $this->transaction->payment_method ?? 'cash';
        $this->typeChange = $this->getTypeChange();
        $this->calculateTotalToPay();
        $this->getTotalPay();
        $this->getTypeChange();
    }

    public function updatedCash($value): void
    {
        $this->cash = (int) preg_replace('/[^\d]/', '', $value);
        $this->calculateChange();
    }

    public function calculateChange(): void
    {
        $this->change = max(0, $this->cash - $this->total);
    }

    public function getTypeChange(): string
    {
        $sale = $this->transaction->sale->total_amount ?? 0;
        $purchase = $this->transaction->purchase->total_amount ?? 0;

        return $sale > $purchase ? 'sale' : 'purchase';
    }

    public function calculateTotalToPay(): void
    {
        $this->total = $this->transaction->amount ?? 0;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            Split::make([
                Section::make([
                    Placeholder::make('orders')
                        ->label('')
                        ->content(new HtmlString(
                            view('filament.pages.shop.change.payment-detail', [
                                'record' => $this->transaction,
                            ])->render()
                        )),
                ]),
                Section::make([
                    $this->getCounterPlaceholder(),
                    $this->getPaymentMethodRadio(),
                    $this->getSubmitAction(),
                ]),
            ]),
        ];
    }

    protected function getCounterPlaceholder(): Placeholder
    {
        return Placeholder::make('counter')
            ->label('')
            ->content(fn() => new HtmlString(
                view('filament.pages.shop.change.counter', [
                    'state' => $this->transaction,
                    'change' => $this->change,
                    'type' => $this->typeChange,
                    'method' => $this->data['payment_method'] ?? 'cash',
                ])->render()
            ));
    }

    protected function getPaymentMethodRadio(): Radio
    {
        return Radio::make('payment_method')
            ->label('Metode Pembayaran')
            ->options([
                'cash' => 'Tunai',
                'online' => 'Transfer',
            ])
            ->default($this->data['payment_method'] ?? 'cash')
            ->reactive()
            ->afterStateUpdated(fn($state) => $this->updatePaymentMethod($state))
            ->inline();
    }

    protected function getSubmitAction(): Actions
    {
        return Actions::make([
            FormAction::make('submit')
                ->label(fn() => 'Bayar Rp. ' . number_format($this->total, 0, ',', '.'))
                ->icon('heroicon-m-credit-card')
                ->disabled(fn() => $this->typeChange === 'sale' && $this->data['payment_method'] === 'cash' && $this->cash < $this->total)
                ->button()
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembayaran')
                ->modalSubheading('Apakah Anda yakin ingin melanjutkan pembayaran?')
                ->modalButton('Konfirmasi')
                ->action(fn() => $this->processPayment())
        ])->fullWidth();
    }

    public function updatePaymentMethod(string $method): void
    {
        $this->data['payment_method'] = $method;

        $this->transaction->update([
            'payment_method' => $method,
        ]);

        Notification::make()
            ->title('Metode Pembayaran Diperbarui')
            ->success()
            ->duration(3000)
            ->send();
    }

    public function processPayment(): void
    {
        // 🛑 Kurangi stok (sale)
        foreach ($this->transaction->sale->saleDetails as $saleDetail) {
            $productId = $saleDetail->product_id;
            $qty = $saleDetail->quantity;

            $stockTotal = StockTotal::firstOrCreate(
                ['product_id' => $productId],
                ['total' => 0]
            );

            $stockTotal->decrement('total', $qty);
        }

        // ✅ Tambah stok baru (purchase)
        foreach ($this->transaction->purchase->purchaseDetails as $purchaseDetail) {
            $productId = $purchaseDetail->product_id;
            $qty = $purchaseDetail->quantity;

            // Insert ke tabel stocks (log historis)
            Stock::create([
                'product_id'    => $productId,
                'supplier_id'   => null, // karena dari customer
                'stock_quantity' => $qty,
                'received_at'   => Carbon::now(),
            ]);

            // Update total stok
            $stockTotal = StockTotal::firstOrCreate(
                ['product_id' => $productId],
                ['total' => 0]
            );

            $stockTotal->increment('total', $qty);
        }

        if ($this->typeChange = "sale") {
            $this->transaction->sale->update([
                'cash' => $this->cash,
                'change' => $this->change,
            ]);
        }

        // 🔄 Update transaksi jadi sukses
        $this->transaction->update([
            'status' => 'success',
            'payment_method' => $this->data['payment_method'],
            'total_amount' => $this->total,
            'transaction_date' => now()
        ]);

        Notification::make()
            ->title('Pembayaran Berhasil Diproses')
            ->success()
            ->send();

        $this->redirect(ChangeResource::getUrl('index'));
    }

    public function getHeading(): string|Htmlable
    {
        return $this->getTitleFromType();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getTitleFromType();
    }

    public function getBreadcrumb(): string
    {
        return $this->getTitleFromType();
    }

    public function getTotalPay(): void
    {
        $sale = $this->transaction->sale->total_amount ?? 0;
        $purchase = $this->transaction->purchase->total_amount ?? 0;

        // Total pembayaran adalah yang paling besar
        // $this->total = max($sale, $purchase);

        // Simpan selisih antar dua tabel
        $this->total = abs($sale - $purchase);
    }

    protected function getTitleFromType(): string
    {
        return $this->typeChange === 'sale'
            ? 'Transaksi Tukar Tambah'
            : 'Transaksi Tukar Kurang';
    }
}
