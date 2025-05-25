<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Filament\Forms\Form;
use App\Models\Transaction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Radio;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;

class PaymentPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PurchaseResource::class;
    protected static string $view = 'filament.pages.shop.purchase.payment';
    protected static ?string $title = 'Invoice';
    protected static ?string $breadcrumb = 'Invoice';

    public Transaction $record;
    public ?array $data = [];



    public function mount(string $invoice): void
    {
        if (!$invoice) {
            $this->redirect(route('filament.admin.shop.resources.purchases.index'));
            return;
        }

        $this->loadPage($invoice);
        $this->form->fill();
    }

    public function loadPage(string $invoice): void
    {
        $this->record = Transaction::where('invoice', $invoice)->first();
        $this->redirectBack($this->record);

        $this->record->status = 'success';
        $this->data['payment_method'] = $this->record->payment_method;
    }

    public function redirectBack(Transaction $record): void
    {
        if ($record->status === 'success') {
            redirect()->route('filament.admin.shop.resources.purchases.index');
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
            Card::make([
                Placeholder::make('orders')
                    ->label('')
                    ->content(new HtmlString(
                        view('filament.pages.shop.purchase.detail', [
                            'record' => $this->record,
                        ])->render()
                    )),
                Radio::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Tunai',
                        'online' => 'Transfer',
                    ])
                    ->default($this->data['payment_method'])
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->paymentMethod($state);
                    })
                    ->inline(),

                Placeholder::make('orders')
                    ->label('')
                    ->content(new HtmlString(
                        view('filament.pages.shop.purchase.submit')->render()
                    )),
            ]),
        ];
    }

    public function paymentMethod(string $method): void
    {
        $this->data['payment_method'] = $method;
        $this->record->update(['payment_method' => $method]);

        $this->loadPage($this->record->invoice);

        Notification::make()
            ->title('Metode Pembayaran Berhasil Diubah')
            ->success()
            ->duration(3000)
            ->send();
    }

    public function processPayment(): mixed
    {
        try {
            $this->record->update([
                'status' => 'success',
                'payment_method' => $this->data['payment_method'],
                'transaction_date' => now(),
            ]);

            Notification::make()
                ->title('Pembelian berhasil')
                ->body("Pembelian berhasil untuk invoice #{$this->record->invoice}")
                ->success()
                ->send();

            return redirect()->route('filament.admin.shop.resources.purchases.index');
        } catch (\Throwable $e) {
            Log::error('Gagal memproses Pembelian', [
                'invoice' => $this->record->invoice ?? null,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Terjadi kesalahan saat memproses Pembelian')
                ->danger()
                ->persistent()
                ->send();

            return null;
        }
    }
}
