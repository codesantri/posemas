<?php

namespace App\Filament\Clusters\Shop\Resources\PawningResource\Pages;

use App\Filament\Clusters\Shop\Resources\PawningResource;
use App\Models\Transaction;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;

class PaymentPage extends Page implements HasForms
{
    protected static string $resource = PawningResource::class;
    protected static string $view = 'filament.pages.shop.pawning.payment-page';
    protected static ?string $title = 'Pembayaran Penggadaian';
    protected static ?string $breadcrumb = 'Pembayaran';

    public Transaction $record;
    public ?array $data = [];
    public ?int $cash = 0;
    public ?int $change = 0;
    public ?int $total = 0;

    protected $queryString = ['payment_method'];
    protected $listeners = ['updatedCash'];

    public function mount(string $inv): void
    {
        if (!$inv) {
            $this->redirect(route('filament.admin.shop.resources.pawnings.index'));
            return;
        }

        $this->loadPage($inv);
        $this->form->fill();
    }

    public function updatedCash($value): void
    {
        // Clean input: remove all non-digit characters (like dots/commas)
        $cleanValue = (int) preg_replace('/[^\d]/', '', $value);

        // Assign to $cash which is of type int
        $this->cash = $cleanValue;

        // Recalculate total and change
        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->change = max(0, $this->cash - $this->total);
    }

    public function loadPage(string $inv): void
    {
        $this->record = Transaction::where('invoice', $inv)
            ->where('transaction_type', 'pawning')
            ->firstOrFail();
        $this->record->status = $this->record->status ?? 'pending';
        $this->data['payment_method'] = $this->record->payment_method;
        $this->total = $this->record->total_amount;
    }

    public function redirectBack(Transaction $record): void
    {
        if ($record->status === 'success') {
            redirect()->route('filament.admin.shop.resources.pawnings.index');
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
            Split::make([
                Section::make([
                    Placeholder::make('orders')
                        ->label('')
                        ->content(new HtmlString(
                            view('filament.pages.shop.pawning.payment-detail', [
                                'record' => $this->record,
                            ])->render()
                        )),
                ]),
                Section::make([
                    Placeholder::make('orders')
                        ->label('')
                        ->content(function () {
                            return new HtmlString(
                                view('filament.pages.shop.pawning.counter', [
                                    'state' => $this->record,
                                    'change' => $this->change,
                                    'method' => $this->record->payment_method
                                ])->render()
                            );
                        }),
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
                    Actions::make([
                        FormAction::make('submit')
                            ->label('Bayar Rp.' . number_format($this->total, 0, ',', '.'))
                            ->icon('heroicon-m-credit-card')
                            ->disabled(fn() => $this->cash < $this->total && $this->record->payment_method != 'online')
                            ->button()
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading('Konfirmasi Pembayaran')
                            ->modalSubheading('Apakah Anda yakin ingin melanjutkan pembayaran?')
                            ->modalButton('Konfirmasi')
                            ->action(fn() => $this->paymentPawning())
                            ->visible(fn() => $this->record->status !== 'success'),
                    ])->fullWidth(),
                ]),
            ]),
        ];
    }

    public function paymentMethod(string $method): void
    {
        $this->data['payment_method'] = $method;
        $this->record->update(['payment_method' => $method]);
        $this->cash = 0;
        $this->change = 0;

        $this->loadPage($this->record->invoice);

        Notification::make()
            ->title('Metode Pembayaran Berhasil Diubah')
            ->success()
            ->duration(3000)
            ->send();
    }

    public function paymentPawning()
    {
        if (!$this->record->invoice || $this->record->status !== 'pending') {
            Notification::make()
                ->title('Transaksi tidak ditemukan')
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessage = collect($e->validator->errors()->all())->first();

            Notification::make()
                ->title('Gagal Validasi')
                ->body($errorMessage)
                ->danger()
                ->duration(3000)
                ->send();

            return;
        }

        if ($this->cash < $this->total) {
            Notification::make()
                ->title('Nominal pembayaran kurang dari total yang harus dibayar')
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }

        $this->record->update([
            'payment_method' => $this->data['payment_method'],
            'status' => 'success',
            'transaction_date' => now(),
            'total_amount' => $this->total,
        ]);

        $this->record->pawning->update([
            'status' => "paid_off",
        ]);


        Notification::make()
            ->title('Pembayaran Berhasil')
            ->success()
            ->duration(3000)
            ->send();
        return redirect()->route('filament.admin.shop.resources.pawnings.index');
    }
}
