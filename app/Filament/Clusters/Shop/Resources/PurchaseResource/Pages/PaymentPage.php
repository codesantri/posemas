<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use App\Models\Stock;
use Filament\Forms\Form;
use App\Models\StockTotal;
use App\Models\Transaction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Actions;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;

class PaymentPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PurchaseResource::class;
    protected static string $view = 'filament.pages.shop.purchase.payment';
    protected static ?string $title = 'Invoice';
    protected static ?string $breadcrumb = 'Invoice';

    public Transaction $record;
    public ?string $invoice = null;
    public ?array $data = [];
    public float $total;

    public function mount(string $invoice): void
    {
        $this->invoice = $invoice;

        if (empty($invoice)) {
            $this->redirectToIndex();
        }

        $this->loadPage($invoice);
        $this->total = $this->record->total_amount ?? 0;
        $this->form->fill();
    }

    protected function redirectToIndex()
    {
        return redirect()->route('filament.admin.shop.resources.purchases.index');
    }


    public function loadPage(string $invoice): void
    {
        $this->record = Transaction::with(['purchase.purchaseDetails.product'])
            ->where('invoice', $invoice)
            ->firstOrFail();

        $this->validateTransactionStatus();

        $this->data = [
            'payment_method' => $this->record->payment_method ?? 'cash',
        ];
    }

    protected function validateTransactionStatus(): void
    {
        if ($this->record->status === 'success') {
            $this->redirectToIndex();
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
            Card::make()
                ->schema([
                    $this->getOrderDetailsComponent(),
                    $this->getPaymentMethodComponent(),
                    $this->getPaymentAction(),
                ])
        ];
    }

    protected function getOrderDetailsComponent(): Placeholder
    {
        return Placeholder::make('order_details')
            ->label('')
            ->content(new HtmlString(
                view('filament.pages.shop.purchase.detail', [
                    'record' => $this->record,
                ])->render()
            ));
    }

    protected function getPaymentMethodComponent(): Radio
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

   

    protected function getPaymentAction(): Actions
    {
        return Actions::make([
            FormAction::make('submit')
                ->label('Bayar Rp.' . number_format($this->total, 0, ',', '.'))
                ->icon('heroicon-m-credit-card')
                ->button()
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembayaran')
                ->modalSubheading('Apakah Anda yakin ingin melanjutkan pembayaran?')
                ->modalButton('Konfirmasi')
                ->action(fn() => $this->processPayment())
                ->visible(fn() => $this->record->status !== 'success'),
        ])->fullWidth();
    }

    public function updatePaymentMethod(string $method): void
    {
        try {
            $this->record->update(['payment_method' => $method]);
            $this->data['payment_method'] = $method;

            Notification::make()
                ->title('Metode Pembayaran Berhasil Diubah')
                ->success()
                ->duration(3000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal mengubah metode pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function processPayment(): void
    {
        DB::beginTransaction();

        try {
            $this->updateTransactionStatus();
            $this->updateProductStocks();

            DB::commit();

            $this->sendSuccessNotification();
            $this->redirectToIndex();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->handlePaymentError($e);
        }
    }

    protected function updateTransactionStatus(): void
    {
        $this->record->update([
            'status' => 'success',
            'payment_method' => $this->data['payment_method'],
            'transaction_date' => now(),
        ]);
    }

    protected function updateProductStocks(): void
    {
        foreach ($this->record->purchase->purchaseDetails as $detail) {
            $this->updateProductStock($detail->product_id, $detail->quantity);
        }
    }

    protected function updateProductStock(int $productId, int $quantity): void
    {
        DB::transaction(function () use ($productId, $quantity) {
            // Update stock total atomically
            StockTotal::updateOrCreate(
                ['product_id' => $productId],
                ['total' => DB::raw("COALESCE(total, 0) + {$quantity}")]
            );

            // Record stock history
            Stock::create([
                'product_id' => $productId,
                'stock_quantity' => $quantity,
                'received_at' => now(),
            ]);
        });
    }

    protected function sendSuccessNotification(): void
    {
        Notification::make()
            ->title('Pembelian berhasil')
            ->body("Pembelian berhasil untuk invoice #{$this->record->invoice}")
            ->success()
            ->send();
    }

    protected function handlePaymentError(\Throwable $e): void
    {
        Log::error('Gagal memproses Pembelian', [
            'invoice' => $this->record->invoice,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        Notification::make()
            ->title('Terjadi kesalahan saat memproses Pembelian')
            ->body($e->getMessage())
            ->danger()
            ->persistent()
            ->send();
    }
}
